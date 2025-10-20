<?php

/**
 * Vinexel Framework.
 *
 * @package Vision
 * @author Elwira Perdana
 * @copyright (c) PT Iconic Wira Niaga
 * @license MIT License
 * @see https://vinexel.com/documentation/global/functions
 */
// Tangani pengaturan bahasa dari URL
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    setcookie('lang', $lang, time() + (86400 * 30), '/'); // Simpan cookie selama 30 hari
    $_COOKIE['lang'] = $lang; // Segera berlaku pada request saat ini juga
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?')); // Redirect tanpa query string
    exit;
}
