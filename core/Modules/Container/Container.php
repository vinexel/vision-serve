<?php

/**
 * Vinexel Framework
 *
 * @package Vision
 * @author Elwira Perdana
 * @copyright (c) PT Iconic Wira Niaga
 * @license MIT License
 */

namespace Vinexel\Modules\Container;

use Vision\Modules\Database;

class Container
{
    protected static $instances = [];
    protected static $factories = [];
    protected static $configurations = [];

    /**
     * Save instance from service.
     *
     * @param string $abstract
     * @param mixed $factory
     */
    public static function singleton($abstract, $factory)
    {
        self::$factories[$abstract] = $factory;
    }

    /**
     *Save configuration from service.
     *
     * @param string $key
     * @param mixed $value
     */
    public static function configure($key, $value)
    {
        self::$configurations[$key] = $value;
    }

    /**
     * Get configuration from service.
     *
     * @param string $key
     * @return mixed
     * @throws \Exception
     */
    public static function getConfiguration($key)
    {
        if (!isset(self::$configurations[$key])) {
            throw new \Exception("Configuration {$key} not found.");
        }

        return self::$configurations[$key];
    }

    /**
     * Get instance from service.
     *
     * @param string $abstract
     * @return mixed
     * @throws \Exception
     */
    public static function get($abstract)
    {
        // Return instance if already initialization
        if (isset(self::$instances[$abstract])) {
            return self::$instances[$abstract];
        }

        if (class_exists($abstract)) {
            $reflection = new \ReflectionClass($abstract);

            if ($reflection->isSubclassOf(ucfirst(PROJECT_NAME) . '\BaseModel')) {
                $instance = new $abstract(self::get(Database::class)); // Inject Database ke dalam model
                self::$instances[$abstract] = $instance;
                return $instance;
            }
        }

        if (!isset(self::$factories[$abstract])) {
            throw new \Exception("Service {$abstract} not found.");
        }

        self::$instances[$abstract] = call_user_func(self::$factories[$abstract]);
        return self::$instances[$abstract];
    }

    /**
     * Get BaseModel.
     *
     * @param string $abstract
     * @return string
     */
    protected static function getBaseModelClass($abstract)
    {
        $namespaceParts = explode('\\', $abstract);

        $projectNamespace = $namespaceParts[0];

        $baseModelClass = $projectNamespace . '\\Models\\BaseModel';

        if (!class_exists($baseModelClass)) {
            throw new \Exception("BaseModel for project {$projectNamespace} not found.");
        }

        return $baseModelClass;
    }

    /**
     * Delete instance service.
     *
     * @param string $abstract
     */
    public static function forget($abstract)
    {
        unset(self::$instances[$abstract]);
    }

    /**
     * Resolve service from container.
     *
     * @param string $abstract
     * @return mixed
     * @throws \Exception
     */
    public static function resolve($abstract)
    {
        return self::get($abstract);
    }

    /**
     * Save and set factory from service need dependencies.
     *
     * @param string $abstract
     * @param callable $factory
     */
    public static function factory($abstract, callable $factory)
    {
        self::$factories[$abstract] = $factory;
    }
}
