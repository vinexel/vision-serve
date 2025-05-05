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

header("X-Frame-Options: SAMEORIGIN");

$block_user_agents = [
    'HTTrack',
    'WebZip',
    'wget',
    'curl',
    'Saveweb2zip',
    'Java',
    'Python',
    'node-fetch',
    'scrapy'
];

$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

foreach ($block_user_agents as $bot) {
    if (stripos($user_agent, $bot) !== false) {
        header("HTTP/1.1 403 Forbidden");
        exit("Access Denied 1.");
    }
}

if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) || !empty($_SERVER['HTTP_VIA'])) {
    header("HTTP/1.1 403 Forbidden");
    exit("Access Denied 2.");
}

$ip = $_SERVER['REMOTE_ADDR'];
$limit = 50;

if (!isset($_SESSION['visitor_count'])) {
    $_SESSION['visitor_count'] = 1;
    $_SESSION['first_visit'] = time();
} else {
    $_SESSION['visitor_count']++;
}

if ($_SESSION['visitor_count'] > $limit && (time() - $_SESSION['first_visit']) < 600) {
    file_put_contents("blocked_ips.txt", $ip . PHP_EOL, FILE_APPEND);
    header("HTTP/1.1 403 Forbidden");
    exit("You blocked.");
}