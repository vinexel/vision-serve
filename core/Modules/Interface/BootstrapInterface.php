<?php

/**
 * Vinexel Framework
 *
 * @package Vision
 * @author Elwira Perdana
 * @copyright (c) PT Iconic Wira Niaga
 * @license MIT License
 */

namespace Vinexel\Modules\Interface;

use \Vision\Modules\Vendor;
use \Vision\Modules\Environment;
use \Deeper\Libraries\Session;
use \System\Projects;

Session::initSecureSession();

/**
 * Marker interface for lazily initialized project, required and additional files function.
 */

interface BootstrapInterface {}

$urlDomain = $_SERVER['HTTP_HOST'];
$projects = new Projects();

try {
    $project = $projects->getProjectName($urlDomain);
    define('PROJECT_NAME', ucfirst(strtolower($project)));
    define('WEBSITE_URI', $urlDomain);
    // if (Environment::shouldValidateTransmitter()) {
    //     $verify = new Vendor();
    //     $verify->check();
    // }
} catch (\Exception $e) {
    $this->handleException($e);
}

$requiredFiles = [
    DIRECTORY_SEPARATOR
        . 'app'
        . DIRECTORY_SEPARATOR
        . PROJECT_NAME
        . DIRECTORY_SEPARATOR
        . ucfirst('libraries')
        . DIRECTORY_SEPARATOR
        . ucfirst('config')
        . DIRECTORY_SEPARATOR
        . 'Constant.php',
    DIRECTORY_SEPARATOR
        . 'app'
        . DIRECTORY_SEPARATOR
        . PROJECT_NAME
        . DIRECTORY_SEPARATOR
        . ucfirst('libraries')
        . DIRECTORY_SEPARATOR
        . ucfirst('config')
        . DIRECTORY_SEPARATOR
        . 'LoadFile.php',
    DIRECTORY_SEPARATOR
        . 'system'
        . DIRECTORY_SEPARATOR
        . 'framework'
        . DIRECTORY_SEPARATOR
        . ucfirst('fragments')
        . DIRECTORY_SEPARATOR
        . ucfirst('config')
        . DIRECTORY_SEPARATOR
        . 'Constants.php',
    DIRECTORY_SEPARATOR
        . 'app'
        . DIRECTORY_SEPARATOR
        . PROJECT_NAME
        . DIRECTORY_SEPARATOR
        . ucfirst('routes')
        . DIRECTORY_SEPARATOR
        . 'InternalRoutes.php',
    DIRECTORY_SEPARATOR
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
        . ucfirst('modules')
        . DIRECTORY_SEPARATOR
        . ucfirst('debug')
        . DIRECTORY_SEPARATOR
        . 'Debug.php',
];

$additionalFiles = [
    DIRECTORY_SEPARATOR
        . 'app'
        . DIRECTORY_SEPARATOR
        . PROJECT_NAME
        . DIRECTORY_SEPARATOR
        . ucfirst('libraries')
        . DIRECTORY_SEPARATOR
        . ucfirst('service')
        . DIRECTORY_SEPARATOR
        . 'Request.php',
    DIRECTORY_SEPARATOR
        . 'system'
        . DIRECTORY_SEPARATOR
        . 'framework'
        . DIRECTORY_SEPARATOR
        . ucfirst('fragments')
        . DIRECTORY_SEPARATOR
        . ucfirst('gateway')
        . DIRECTORY_SEPARATOR
        . 'Maintenance.php',
];

foreach ($requiredFiles as $filePath) {
    $fullPath = VISION_DIR . $filePath;
    if (file_exists($fullPath)) {
        require_once $fullPath;
    } else {
        die("Error: Required main files not found: {$filePath}");
    }
}

foreach ($additionalFiles as $filePath) {
    $fullPath = VISION_DIR . $filePath;
    if (file_exists($fullPath)) {
        require_once $fullPath;
    }
}
