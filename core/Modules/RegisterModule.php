<?php

/**
 * Vinexel Framework.
 *
 * @package Vision
 * @author Elwira Perdana
 * @copyright (c) PT Iconic Wira Niaga
 * @license MIT License
 */

namespace Vinexel\Modules;

use Vinexel\Modules\Interface\BootstrapInterface;
use \Vinexel\Modules\Middleware\Restrictions;
use Vinexel\Modules\Router\RouterInitializer;
use Vinexel\Modules\Globals\Language\Lang;
use Vinexel\Modules\Debug\DebugRestrict;
use Vision\Modules\ServiceProvider;
use Vision\Modules\Requirements;
use Vision\Modules\Container;
use Vision\Modules\Loader;
use Vision\Modules\Router;

/**
 * Class RegisterModule
 *
 * Base abstract class that defines the bootstrapping process for a Vision module
 * within the Vinexel Framework. It manages lifecycle initialization, routing,
 * dependency injection, middleware, and localization.
 *
 * The class implements a singleton-style lifecycle that ensures
 * the module is initialized only once during a request.
 */
abstract class RegisterModule implements BootstrapInterface
{
    /**
     * The current controller name.
     *
     * @var string
     */
    protected $controller = '';

    /**
     * The current method name (default: index).
     *
     * @var string
     */
    protected $method = 'index';

    /**
     * The route parameters.
     *
     * @var array
     */
    protected $params = [];

    /**
     * Internal lifecycle state tracker.
     *
     * @var int
     */
    private static int $lifecycle = 0;

    /**
     * Singleton instance of the current module.
     *
     * @var self|null
     */
    private static ?self $instance = null;

    /**
     * Router instance handler.
     *
     * @var Router|null
     */
    public ?Router $run = null;

    /**
     * Initialize and bootstrap the module lifecycle.
     *
     * - Initializes debugging restrictions.
     * - Checks minimum PHP version requirements.
     * - Loads project configuration and service providers.
     * - Initializes language localization and routing.
     * - Triggers lifecycle hooks: onCreate() and onFinish().
     */
    public function __construct()
    {
        if (self::$lifecycle > 0) {
            if ((self::$instance instanceof static) && !($this->run instanceof Router)) {
                $this->run = self::$instance->run;
            }
            return;
        }

        DebugRestrict::init();
        Requirements::MinVersion();
        Loader::ProjectLoader();
        Lang::setLocale();
        ServiceProvider::register();
        RouterInitializer::initialize();

        // Run the main router
        RouterInitializer::run($this->controller, $this->method, $this->params);

        // Lifecycle start hook
        $this->onCreate();

        // Lifecycle finish hook
        $this->onFinish([
            'controller' => $this->controller,
            'method' => $this->method,
            'params' => $this->params
        ]);

        self::$lifecycle = 1;
    }

    /**
     * Retrieve the database instance from the container.
     *
     * @return mixed
     */
    public function getDatabase()
    {
        return Container::get('database');
    }

    /**
     * Get the singleton instance of the module.
     *
     * @return static
     */
    public static function getInstance(): static
    {
        if (!self::$instance instanceof self) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * Manually set the current module instance.
     *
     * @param RegisterModule $app
     * @return static
     */
    public static function setInstance(RegisterModule $app): static
    {
        if ((self::$instance instanceof static) && !($app->run instanceof Router)) {
            $app->run = self::$instance->run;
        }
        self::$instance = $app;
        return self::$instance;
    }

    /**
     * Destructor — triggers the onDestroy lifecycle hook.
     */
    public function __destruct()
    {
        if (self::$lifecycle > 1) {
            return;
        }

        self::$lifecycle = 2;
        $this->onDestroy();
    }

    /**
     * Lifecycle Hook: onCreate
     *
     * Called once the module has been initialized and before routing execution completes.
     * This method can be overridden by child modules to define initialization logic.
     *
     * Example:
     *  - Register middleware
     *  - Sanitize global inputs
     *  - Set custom headers
     *
     * @return void
     */
    protected function onCreate(): void
    {
        // Initialize and run frontend security middleware
        $securityMiddleware = new Restrictions();
        $securityMiddleware->handle();

        // Trim global input arrays
        $_POST = $this->arrayTrim($_POST);
        $_GET = $this->arrayTrim($_GET);

        // Cache-control header
        // header("Cache-Control: max-age=3600, must-revalidate");
    }

    /**
     * Recursively trim all string values in an array.
     *
     * @param array $data
     * @return array
     */
    private function arrayTrim(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = trim($value);
            } elseif (is_array($value)) {
                $data[$key] = $this->arrayTrim($value);
            }
        }
        return $data;
    }

    /**
     * Lifecycle Hook: onFinish
     *
     * Called after routing and request handling are completed.
     * Intended for cleanup, logging, or final event dispatching.
     *
     * @param array $info Information about the executed controller, method, and parameters.
     * @return void
     */
    protected function onFinish(array $info): void {}


    /**
     * Lifecycle Hook: onDestroy
     *
     * Called when the application is shutting down.
     * Used to perform cleanup tasks such as session clearing and memory collection.
     *
     * @return void
     */
    protected function onDestroy(): void
    {
        //1. Clear temporary session data
        if (session_status() === PHP_SESSION_ACTIVE) {
            unset($_SESSION['temp_data']);
        }

        //2. Trigger PHP garbage collection
        gc_collect_cycles();
    }
}
