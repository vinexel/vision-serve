<?php

/**
 * Vinexel Framework.
 *
 * @package Vision
 * @author Elwira Perdana
 * @copyright (c) PT Iconic Wira Niaga
 * @license MIT License
 */

use Vinexel\Modules\Debug\Debugger;
use Vision\Modules\Config;

$mode = Config::get('APP_MODE', 'production');
Debugger::init($mode);

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return;
    }
    throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
});

set_exception_handler(function ($exception) {
    Debugger::logError($exception);
    if (Debugger::isDebugMode()) {
        Debugger::renderError($exception);
    } else {
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
});

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $exception = new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
        Debugger::logError($exception);
        if (Debugger::isDebugMode()) {
            Debugger::renderError($exception);
        } else {
            echo "A critical error occurred. Please contact support.";
        }
    }
});
