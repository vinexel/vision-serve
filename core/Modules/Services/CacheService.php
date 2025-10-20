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

class CacheService
{
    public static function write(string $path, array $data): void
    {
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
    }

    public static function read(string $path): ?array
    {
        if (!file_exists($path)) {
            return null;
        }

        return json_decode(file_get_contents($path), true);
    }
}
