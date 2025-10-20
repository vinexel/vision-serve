<?php

namespace Vinexel\Modules\Globals\Assets;

use Vinexel\Modules\Debug\Debugger;

class AssetsHandler
{
    protected string $project;

    public function __construct()
    {
        $this->project = defined('PROJECT_NAME') ? PROJECT_NAME : '';
    }

    /**
     * Langsung kembalikan URL file asli tanpa cache/minify
     */
    public function getMinifiedUrl(string $file, string $type): string
    {
        $relativePath = ltrim($file, '/');
        $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/' . $relativePath;

        if (!file_exists($fullPath)) {
            Debugger::log("File tidak ditemukan: {$file}", 'WARNING');
            return '/' . $relativePath;
        }

        // Langsung return URL asli, tanpa cache atau modifikasi
        return '/' . $relativePath;
    }
}
