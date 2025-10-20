<?php

/**
 * Vinexel Framework.
 *
 * @package Vision
 * @author Elwira Perdana
 * @copyright (c) PT Iconic Wira Niaga
 * @license MIT License
 */

namespace Deeper\Libraries;

/**
 * Session Class
 *
 * The `Session` library provides a secure and easy-to-use wrapper around PHP’s
 * native session handling features. It adds automatic security measures such as:
 * - `SameSite` cookie policy
 * - HTTPS detection for secure cookies
 * - IP and User-Agent validation to prevent session hijacking
 *
 * This class is designed to be initialized once per request, typically during
 * your framework’s bootstrap or middleware initialization phase.
 */
class Session
{
    /**
     * Start the session securely.
     *
     * @return void
     */
    public static function start(): void
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            ini_set('session.use_strict_mode', 1);
            session_start();
        }
    }

    /**
     * Regenerate the session ID for security reasons.
     *
     * @return void
     */
    public static function regenerate(): void
    {
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    /**
     * Store a value in the session.
     *
     * @param string $key
     * @param mixed  $value
     * @return void
     */
    public static function set($key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Retrieve a value from the session.
     *
     * @param string $key
     * @return mixed|null
     */
    public static function get($key): mixed
    {
        return $_SESSION[$key] ?? null;
    }

    /**
     * Check if a specific session key exists.
     *
     * @param string $key
     * @return bool
     */
    public static function has($key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Remove a specific key from the session.
     *
     * @param string $key
     * @return void
     */
    public static function remove($key): void
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Destroy the current session entirely.
     *
     * @return void
     */
    public static function destroy(): void
    {
        if (session_status() != PHP_SESSION_NONE) {
            session_unset();
            session_destroy();
        }
    }

    /**
     * Validate the current session against client IP and User-Agent.
     * This prevents session hijacking or fixation attacks.
     *
     * @return bool True if the session is valid, false otherwise.
     */
    public static function validateSession(): bool
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';

        if (!self::has('user_agent') || !self::has('ip_address')) {
            self::set('user_agent', $userAgent);
            self::set('ip_address', $ipAddress);
        } else {
            if (self::get('user_agent') !== $userAgent || self::get('ip_address') !== $ipAddress) {
                self::destroy();
                return false;
            }
        }
        return true;
    }

    /**
     * Initialize a secure session with validation.
     * 
     * This method starts the session and validates its integrity.
     * If validation fails (e.g. IP or User-Agent mismatch),
     * you may choose to redirect or terminate the request manually.
     *
     * Note:
     * The redirect below is commented out intentionally to prevent
     * endless refresh loops during development or debugging.
     *
     * Example:
     * ```php
     * Session::initSecureSession();
     * ```
     *
     * @return void
     */
    public static function initSecureSession(): void
    {
        self::start();

        // If validation fails, you can safely redirect or terminate the request (just if you want)!
        if (!self::validateSession()) {
            // Example: Redirect to login or home page
            // Hmm ini buat kesal saya bang:[
            // return transfer('/');
        }
    }
}
