<?php

/**
 * Vinexel Framework.
 *
 * @package Vision
 * @author Elwira Perdana
 * @copyright (c) PT Iconic Wira Niaga
 * @license MIT License
 */

namespace Vinexel\Modules\Request;

use Vinexel\Modules\Debug\DebugHandler;

class VendorHandler
{
    public function handleRequest()
    {
        $requestUri = $_SERVER['REQUEST_URI'];

        $routes = require VISION_DIR
            . DIRECTORY_SEPARATOR
            . strtolower('app')
            . DIRECTORY_SEPARATOR
            . PROJECT_NAME
            . DIRECTORY_SEPARATOR
            . ucfirst('routes')
            . DIRECTORY_SEPARATOR
            . 'VendorRoutes.php';

        $restrict = new DebugHandler();
        $projects = $routes['vendor_routes'];
        $vendorPath = $routes['vendor_path'];

        foreach ($projects as $alias => $realPath) {
            if (strpos($requestUri, $alias) === 0) {
                $newUri = str_replace($alias, $realPath, $requestUri);
                $_SERVER['REQUEST_URI'] = $newUri;
                if (!$restrict->isEnabled()) {
                    echo RESTRICTION;
                }
                require $vendorPath;
                exit;
            }
        }
    }
}
