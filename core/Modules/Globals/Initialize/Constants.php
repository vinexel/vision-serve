<?php

declare(strict_types=1);
/**
 * Vinexel Framework.
 *
 * @package Vision
 * @author Elwira Perdana
 * @copyright (c) PT Iconic Wira Niaga
 * @license MIT License
 */

use \Deeper\Globals\Config\Src\SysCon as SC;
use \Vinexel\Modules\Globals\Language\Lang;
use \Vinexel\Modules\Router\Router;

if (!function_exists('transfer')) {
    /**
     * Redirect ke URL yang ditentukan dan langsung exit.
     *
     * @param string $url destination URL (default)
     */
    function transfer(string $url)
    {
        header("Location: " . $url);
        exit();
    }
}

// Di dalam helper routes()
if (!function_exists('route')) {
    function routes($name)
    {
        return Router::getUriByName($name) ?? '#';
    }
}


if (!function_exists('__')) {
    function __($key, $replace = [], $default = null)
    {
        return Lang::get($key, $default, $replace);
    }
}

/**
 * @var string
 */
defined('AUTHOR') || define('AUTHOR', 'Elwira Perdana');

/**
 * @var string
 */
defined('COMPANY') || define('COMPANY', 'Iconic Group');

/**
 * @var string
 */
defined('REQUIRED_PHP_VERSION') || define('REQUIRED_PHP_VERSION', '8.3');

/**
 * @var string
 */
defined('VINEXEL_VERSION') || define('VINEXEL_VERSION', '1.0.0');

/**
 * @var string
 */
defined('VINSTALL') || define('VINSTALL', 'installer');

define('VISION_DIR', dirname(__DIR__, 9));

define(
    'PROJECT_PATH',
    VISION_DIR .
        DIRECTORY_SEPARATOR .
        strtolower(
            SC::get('M_Z') .
                SC::get('K_M') .
                SC::get('K_M')
        ) .
        DIRECTORY_SEPARATOR
);

define(
    'F_R',
    SC::get('T_D') .
        SC::get('I_M') .
        SC::get('M_Z') .
        SC::get('K_M') .
        SC::get('M_R') .
        SC::get('W_M') .
        SC::get('T_D') .
        SC::get('K_M') .
        SC::get('S_M') .
        SC::get('K_M')
);

define(
    'F_P',
    SC::get('K_M') .
        SC::get('S_M') .
        SC::get('K_M')
);

define(
    'PORT_DEFAULT',
    SC::get('B_W') .
        SC::get('J_W') .
        SC::get('J_W') .
        SC::get('J_W')
);

define(
    'VI',
    'MalScan by &rsaquo; VisionIconic'
);

define(
    'MS0',
    'title'
);

define(
    'MS1',
    'Dashboard'
);

define(
    'MS2',
    'index'
);

define(
    'RESTRICTION',
    '<script>' .
        file_get_contents(
            VISION_DIR
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
                . 'js'
                . DIRECTORY_SEPARATOR
                . 'Restrict.js'
        )
        . '</script>'
);
