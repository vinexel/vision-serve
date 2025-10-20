<?php

namespace Vinexel\Modules\Globals\Assets;

class AssetsManager
{
    protected static string $baseAssetPath;

    protected static function init(): void
    {
        if (!isset(self::$baseAssetPath)) {
            self::$baseAssetPath = 'static/' . strtolower(PROJECT_NAME) . '/';
        }
    }

    public static function setBaseAssetPath(string $path): void
    {
        self::$baseAssetPath = rtrim($path, '/') . '/';
    }

    public static function asset(string $path): string
    {
        self::init(); // pastikan base path di-set

        $fullPath = self::$baseAssetPath . ltrim($path, '/');
        $minifier = new AssetsHandler();

        try {
            return $minifier->getMinifiedUrl($fullPath, strtolower(pathinfo($fullPath, PATHINFO_EXTENSION)));
        } catch (\Exception $e) {
            return '/' . $fullPath;
        }
    }
}
