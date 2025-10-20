<?php

/**
 * Vinexel Framework.
 *
 * @package Vision
 * @author Elwira Perdana
 * @copyright (c) PT Iconic Wira Niaga
 * @license MIT License
 */

namespace Vinexel\Modules\Globals\Language;

class Lang
{
    protected static $translations = [];
    protected static $locale = 'en';

    public static function setLocale($locale = null)
    {
        // Ambil dari cookie jika tidak dikirim langsung
        if (!$locale) {
            $locale = $_COOKIE['lang'] ?? self::$locale;
        }

        $file = VISION_DIR . '/app/' . PROJECT_NAME . "/Libraries/Language/{$locale}.php";

        // Cek jika file bahasa tidak ditemukan
        if (!file_exists($file)) {
            $locale = self::$locale; // fallback ke default 'en'
            $file = VISION_DIR . '/app/' . PROJECT_NAME . "/Libraries/Language/{$locale}.php";
        }

        self::$locale = $locale;
        self::$translations = file_exists($file) ? require $file : [];

        // Atur cookie jika belum ada
        if (!isset($_COOKIE['lang'])) {
            setcookie('lang', $locale, time() + (86400 * 30), "/");
        }
    }


    public static function get($key, $default = null, array $replace = [])
    {
        $text = self::getFromNestedKey(self::$translations, $key);

        if (is_array($text)) {
            return $default ?? $key;
        }

        if ($text === null) {
            $text = $default ?? $key;
        }

        foreach ($replace as $k => $v) {
            $text = str_replace(":$k", $v, $text);
        }

        return $text;
    }

    protected static function getFromNestedKey(array $array, string $key)
    {
        $segments = explode('.', $key);
        foreach ($segments as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return null;
            }
            $array = $array[$segment];
        }
        return $array;
    }

    public static function getLocale()
    {
        return self::$locale;
    }
}
