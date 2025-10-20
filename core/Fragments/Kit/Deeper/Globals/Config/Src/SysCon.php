<?php

/**
 * Vinexel Framework.
 *
 * @package Vision
 * @author Elwira Perdana
 * @copyright (c) PT Iconic Wira Niaga
 * @license MIT License
 */

namespace Deeper\Globals\Config\Src;

use Exception;

class SysCon
{
    private static $data = [];

    public static function load($filePath)
    {
        if (!file_exists($filePath)) {
            throw new Exception('TXT file not found.');
        }
        $data = [];
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                $value = trim($value, '"');
                $data[$key] = $value;
            }
        }
        if (empty($data)) {
            throw new Exception('Error parsing TXT file. Please check the syntax.');
        }

        self::$data = $data;
    }
    public static function get($key, $default = null)
    {
        return isset(self::$data[$key]) ? self::$data[$key] : $default;
    }
}

$atoz = dirname(__DIR__, 12)
    . DIRECTORY_SEPARATOR
    . strtolower('system')
    . DIRECTORY_SEPARATOR
    . strtolower('vendor')
    . DIRECTORY_SEPARATOR
    . strtolower('plugins')
    . DIRECTORY_SEPARATOR
    . strtolower('vinexel')
    . DIRECTORY_SEPARATOR
    . strtolower('vision-serve')
    . DIRECTORY_SEPARATOR
    . strtolower('core')
    . DIRECTORY_SEPARATOR
    . ucfirst('fragments')
    . DIRECTORY_SEPARATOR
    . ucfirst('kit')
    . DIRECTORY_SEPARATOR
    . ucfirst('deeper')
    . DIRECTORY_SEPARATOR
    . ucfirst('globals')
    . DIRECTORY_SEPARATOR
    . ucfirst('environment')
    . DIRECTORY_SEPARATOR
    . ucfirst('src')
    . DIRECTORY_SEPARATOR
    . 'AtoZ.txt';
SysCon::load($atoz);
