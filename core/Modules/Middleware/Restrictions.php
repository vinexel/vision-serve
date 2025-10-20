<?php

/**
 * Vinexel Framework
 *
 * @package Vision
 * @author Elwira Perdana
 * @copyright (c) PT Iconic Wira Niaga
 * @license MIT License
 */

namespace Vinexel\Modules\Middleware;

use App\RegisterProjects;
use Vinexel\Modules\Debug\Debugger;

class Restrictions
{
    /**
     * List of allowed origins (domains).
     *
     * @var array
     */
    protected $allowedOrigins = [];

    /**
     * Initialize the restriction system by registering allowed domains.
     */
    public function __construct()
    {
        // Get domain list from RegisterProjects and make array url http(s)
        $domains = RegisterProjects::getDomains(); // array [domain => projectName]

        $this->allowedOrigins = [];
        foreach (array_keys($domains) as $domain) {
            $this->allowedOrigins[] = 'https://' . $domain;
            $this->allowedOrigins[] = 'http://' . $domain;
        }
    }

    /**
     * List of disallowed (blocked) User-Agents.
     *
     * @var array
     */
    protected $blockedUserAgents = [
        // CLI-based clients
        'curl',
        'wget',
        'httpie',
        'fetch',
        'powershell',
        'libwww',
        'go-http-client',
        'httpclient',

        // Scrapers & automation
        'httrack',
        'scrapy',
        'python',
        'urllib',
        'aiohttp',
        'lwp',
        'perl',
        'java',
        'jakarta',
        'phantomjs',
        'headless',
        'selenium',
        'puppeteer',
        'playwright',
        'chrome-lighthouse',

        // Downloaders
        'saveweb2zip',
        'webzip',
        'webcopier',
        'teleport pro',
        'sitesnagger',
        'offline explorer',
        'pagenest',

        // General bad actors
        'bot',
        'crawler',
        'spider',
        'analyzer',
        'scanner',
        'masscan',
        'nmap',
        'sqlmap'
    ];

    /**
     * List of allowed (trusted) User-Agents.
     *
     * @var array
     */
    protected $allowedUserAgents = [
        'Mozilla',       // Browser user-agents
        'Chrome',
        'Safari',
        'Firefox',
        'Edge',
        'Opera',
        'Googlebot',     // SEO crawlers
        'Bingbot',
        'DuckDuckBot',
        'YandexBot',
        'Twitterbot',
        'facebookexternalhit',
        'Slackbot',
        'WhatsApp'
    ];

    /**
     * Example: append middleware flag (for debugging or visual inspection).
     */
    public function append()
    {
        echo RESTRICTION;
    }

    /**
     * Main handler for all restriction layers.
     */
    public function handle()
    {
        $this->enforceHeaders();
        $this->blockScrapingBots();
        $this->validateReferer();
        $this->sanitizeQuery();
        $this->rateLimit(); // opsional tapi disarankan
    }

    /**
     * Enforce security headers and detect header spoofing attempts.
     */
    protected function enforceHeaders()
    {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) || !empty($_SERVER['HTTP_VIA'])) {
            Debugger::log('Header spoof attempt', [
                'ip' => $_SERVER['REMOTE_ADDR'],
                'x_forwarded_for' => $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '',
                'via' => $_SERVER['HTTP_VIA'] ?? ''
            ]);
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
            Debugger::log('Too many visits in short time', ['ip' => $ip, 'visits' => $_SESSION['visitor_count']]);
            file_put_contents("blocked_ips.txt", $ip . PHP_EOL, FILE_APPEND);
            header("HTTP/1.1 403 Forbidden");
            exit("You blocked.");
        }

        header("X-Frame-Options: DENY");
        header("X-Content-Type-Options: nosniff");
        header("Referrer-Policy: no-referrer");
        header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
    }

    /**
     * Block known scraping bots and headless clients based on User-Agent.
     */
    protected function blockScrapingBots()
    {
        $userAgent = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');

        foreach ($this->allowedUserAgents as $allowed) {
            if (stripos($userAgent, strtolower($allowed)) !== false) {
                return;
            }
        }

        foreach ($this->blockedUserAgents as $blocked) {
            if (stripos($userAgent, strtolower($blocked)) !== false) {
                Debugger::log('Bot access blocked', [
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                    'ip' => $_SERVER['REMOTE_ADDR']
                ]);
                http_response_code(403);
                exit('Access denied (bot detected).');
            }
        }
    }

    /**
     * Validate the HTTP Referer header to prevent CSRF and cross-domain abuse.
     */
    protected function validateReferer()
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if (!empty($referer)) {
            $valid = false;
            foreach ($this->allowedOrigins as $origin) {
                if (strpos($referer, $origin) === 0) {
                    $valid = true;
                    break;
                }
            }

            if (!$valid) {
                Debugger::log('Invalid referer blocked', [
                    'referer' => $referer,
                    'ip' => $_SERVER['REMOTE_ADDR']
                ]);
                http_response_code(403);
                exit('Invalid referer.');
            }
        }
    }

    /**
     * Sanitize incoming query strings to prevent XSS attacks.
     */
    protected function sanitizeQuery()
    {
        foreach ($_GET as $key => $value) {
            $_GET[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
    }

    /**
     * Basic in-session rate limiting to reduce request abuse.
     */
    protected function rateLimit()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $key = 'frontend_rate_' . md5($ip);
        $limit = 200;
        $window = 300;

        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['count' => 1, 'start' => time()];
        } else {
            if (time() - $_SESSION[$key]['start'] < $window) {
                $_SESSION[$key]['count']++;
                if ($_SESSION[$key]['count'] > $limit) {
                    Debugger::log('Rate limit exceeded', [
                        'ip' => $ip,
                        'count' => $_SESSION[$key]['count']
                    ]);
                    http_response_code(429);
                    exit('Too many requests.');
                }
            } else {
                $_SESSION[$key] = ['count' => 1, 'start' => time()];
            }
        }
    }
}
