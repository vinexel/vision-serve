<?php

/**
 * Vinexel Framework.
 *
 * @package Vision
 * @author Elwira Perdana
 * @copyright (c) PT Iconic Wira Niaga
 * @license MIT License
 */

namespace Vinexel\Modules\Services;

final class EnvironmentValidator
{
    public static function isDevMode(): bool
    {
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'cli';

        $hostWithoutPort = preg_replace('/:\d+$/', '', $host);
        $port = $_SERVER['SERVER_PORT'] ?? null;

        $isLocalDomain = in_array($hostWithoutPort, ['localhost', '127.0.0.1'], true)
            || str_ends_with($hostWithoutPort, '.local')
            || str_ends_with($hostWithoutPort, '.test');

        $isDevPort = $port !== null && ((int) $port >= 8000 && (int) $port <= 9000);

        return $isLocalDomain || $isDevPort;
    }

    public static function isProductionIP(): bool
    {
        $ip = $_SERVER['SERVER_ADDR'] ?? '0.0.0.0';
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }

    public static function shouldValidateFramework(): bool
    {
        if (defined('FRAMEWORK_LICENSE') && strtolower(FRAMEWORK_LICENSE) === FRAMEWORK_TYPE) {
            return false;
        }

        return !self::isDevMode() && self::isProductionIP();
    }
}
