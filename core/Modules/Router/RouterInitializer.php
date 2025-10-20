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

use Vinexel\Modules\Router\Router;

/**
 * Handles the initialization and execution of the routing system
 * within the Vinexel Framework.
 */
class RouterInitializer
{
    /**
     * Configure the controller and model namespaces
     * based on the current project name.
     *
     * This method ensures that the router is aware of the proper
     * namespaces for controllers and models, allowing for
     * dynamic domain-based routing and modular application structure.
     *
     * @throws \RuntimeException if PROJECT_NAME is not defined.
     */
    public static function initialize()
    {
        Router::setNamespace('\\' . PROJECT_NAME . '\\Controllers\\');
        Router::setModelNamespace('\\' . PROJECT_NAME . '\\Models\\');
    }

    /**
     * Executes the routing process based on the current
     * request URI and HTTP method.
     *
     * This method dispatches the request to the appropriate
     * controller action.
     */
    public static function run()
    {
        Router::dispatch();
    }
}
