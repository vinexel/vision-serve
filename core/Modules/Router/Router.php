<?php

/**
 * Vinexel Framework.
 *
 * @package Vision
 * @author Elwira Perdana
 * @copyright (c) PT Iconic Wira Niaga
 * @license MIT License
 */

namespace Vinexel\Modules\Router;

use \Vinexel\Modules\Container\Container;
use \Vision\Modules\Config;

class Router
{
    protected static $routes = [];
    protected static $prefix = '';
    protected static $namespace = '';
    protected static $modelNamespace = '';
    protected static $middleware = [];
    protected static $routeGroups = [];

    /**
     * Set namespace for controller
     */
    public static function setNamespace($namespace)
    {
        self::$namespace = rtrim($namespace, '\\') . '\\';
    }

    /**
     * Set namespace for model
     */
    public static function setModelNamespace($namespace)
    {
        self::$modelNamespace = rtrim($namespace, '\\') . '\\';
    }

    /**
     * Register a new route.
     */
    public static function add($method, $uri, $action, $name = null, $middleware = [])
    {
        $method = strtoupper($method);

        $uri = '/' . ltrim(self::$prefix . '/' . ltrim($uri, '/'), '/');

        if (!$name) {
            $name = $uri;
        }

        $route = compact('method', 'uri', 'action', 'name');
        $route['middleware'] = array_merge(self::$middleware, $middleware);
        self::$routes[] = $route;
    }

    /**
     * Retrieve a route URI by its name.
     */
    public static function getUriByName($name)
    {
        foreach (self::$routes as $route) {
            if ($route['name'] == $name) {
                return $route['uri'];
            }
        }
        return null;
    }

    /**
     * Retrieve a named route URI and replace dynamic parameters.
     */
    public static function getUriByNameWithParams($name, $params = [])
    {
        foreach (self::$routes as $route) {
            if (isset($route['name']) && $route['name'] === $name) {
                $uri = $route['uri'];

                // Cari parameter {id}, {slug}, dll dan ganti dari $params
                if (!empty($params)) {
                    foreach ($params as $key => $value) {
                        $uri = str_replace('{' . $key . '}', $value, $uri);
                    }
                }

                return $uri;
            }
        }

        return null;
    }

    /**
     * Define a route group with shared attributes.
     */
    public static function group($attributes, callable $callback)
    {
        $previousNamespace = self::$namespace;
        $previousMiddleware = self::$middleware;
        $previousPrefix = self::$prefix;

        if (isset($attributes['namespace'])) {
            self::setNamespace($attributes['namespace']);
        }

        if (isset($attributes['middleware'])) {
            self::$middleware = array_merge(self::$middleware, (array) $attributes['middleware']);
        }

        if (isset($attributes['prefix'])) {
            self::$prefix = rtrim($previousPrefix, '/') . '/' . trim($attributes['prefix'], '/');
        }

        $callback();

        self::$namespace = $previousNamespace;
        self::$middleware = $previousMiddleware;
        self::$prefix = $previousPrefix;
    }

    /**
     * Dispatch the incoming HTTP request to the appropriate route.
     */
    public static function dispatch()
    {
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Proxy API requests directly to the Go backend if applicable
        if (strpos($requestUri, '/api/v1/') === 0) {
            self::proxyToGoBackend();
            return;
        }

        $requestMethod = $_SERVER['REQUEST_METHOD'];

        foreach (self::$routes as $route) {
            if ($route['method'] === $requestMethod && preg_match(self::convertToRegex($route['uri']), $requestUri, $matches)) {
                array_shift($matches);

                $params = [];
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = $value;
                    }
                }

                // Execute middleware stack
                foreach ($route['middleware'] as $middleware) {
                    if (!self::handleMiddleware($middleware, $matches)) {
                        return;
                    }
                }

                // Call the route action
                return self::callAction($route['action'], $params);
            }
        }

        return self::handle404($requestUri);
    }

    /**
     * Forward API requests to the Go backend service.
     */
    protected static function proxyToGoBackend()
    {
        $goBackendUrl = Config::get('API_URL') . $_SERVER['REQUEST_URI'];
        $response = file_get_contents($goBackendUrl);

        if ($response !== false) {
            header('Content-Type: application/json');
            echo $response;
        } else {
            http_response_code(502);
            echo json_encode(["error" => "Bad Gateway: Failed to connect to Golang backend"]);
        }
        exit;
    }


    /**
     * Convert URI definition into a regex pattern.
     */
    protected static function convertToRegex($uri)
    {
        // Convert {parameter} into named regex groups
        $uri = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<\1>[a-zA-Z0-9_-]+)', $uri);
        return '#^' . rtrim($uri, '/') . '/?$#';
    }

    /**
     * Execute the route’s assigned action.
     */
    protected static function callAction($action, $params)
    {
        if (is_callable($action)) {
            return call_user_func_array($action, $params);
        }

        list($controller, $method) = explode('@', $action);
        $controller = self::$namespace . $controller;

        if (!class_exists($controller)) {
            return self::sendServerError("Controller '$controller' not found");
        }

        $controllerInstance = new $controller;

        if (!method_exists($controllerInstance, $method)) {
            return self::sendServerError("Method '$method' in controller '$controller' not found");
        }

        return call_user_func_array([$controllerInstance, $method], $params);
    }

    /**
     * Execute middleware for the current route.
     */
    protected static function handleMiddleware($middleware, $params)
    {
        if (is_callable($middleware)) {
            return call_user_func($middleware, ...$params);
        }

        return true;
    }

    /**
     * Handle 404 Not Found error.
     */
    protected static function handle404($requestUri)
    {
        http_response_code(404);
        // echo "404 Not Found: The requested URL '$requestUri' was not found on this server.";
        include VISION_DIR
            . DIRECTORY_SEPARATOR
            . 'system'
            . DIRECTORY_SEPARATOR
            . 'vendor'
            . DIRECTORY_SEPARATOR
            . 'plugins'
            . DIRECTORY_SEPARATOR
            . 'vinexel'
            . DIRECTORY_SEPARATOR
            . 'vision-serve'
            . DIRECTORY_SEPARATOR
            . 'core'
            . DIRECTORY_SEPARATOR
            . ucfirst('fragments')
            . DIRECTORY_SEPARATOR
            . ucfirst('resources')
            . DIRECTORY_SEPARATOR
            . 'view'
            . DIRECTORY_SEPARATOR
            . '404.rapid';
    }

    /**
     * Handle internal server errors (500).
     */
    protected static function sendServerError($message)
    {
        http_response_code(500);
        echo $message;
    }

    /**
     * Dynamically load a model using the configured model namespace.
     */
    public static function models($model)
    {
        $modelClass = self::$modelNamespace . ucfirst($model);
        return Container::get($modelClass);
    }

    /**
     * Send a JSON response.
     */
    public static function json($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
