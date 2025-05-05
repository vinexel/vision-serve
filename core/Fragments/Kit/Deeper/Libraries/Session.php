<?php

namespace Deeper\Libraries;

class Session
{
    public static function start()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public static function get($key)
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }

    public static function remove($key)
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public static function unset($key)
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public static function destroy()
    {
        if (session_status() != PHP_SESSION_NONE) {
            session_unset();    // Menghapus semua variabel sesi
            session_destroy();  // Menghancurkan sesi
        }
    }

    // Menambahkan metode has untuk memeriksa keberadaan kunci sesi
    public static function has($key)
    {
        return isset($_SESSION[$key]);
    }
}
