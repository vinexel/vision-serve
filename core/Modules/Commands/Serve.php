<?php

/**
 * Vinexel Framework.
 *
 * @package Vision
 * @author Elwira Perdana
 * @copyright (c) PT Iconic Wira Niaga
 * @license MIT License
 */

namespace Vinexel\Modules\Commands;

use System\Projects;

class Serve
{
    const COLOR_RESET = "\e[0m";
    const COLOR_GREEN = "\e[32m";
    const COLOR_YELLOW = "\e[33m";

    public function start($port = PORT_DEFAULT)
    {
        $this->showStartMessage('127.0.0.1', $port);

        $host = '127.0.0.1';

        $publicDir = $this->getPublicDirectory();

        if (!is_dir($publicDir)) {
            $this->showError("Directory $publicDir does not exist.");
            exit;
        }

        // Srtar PHP built-in server
        $command = sprintf("php -S %s:%d -t %s", $host, $port, escapeshellarg($publicDir));

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // for Windows
            $command .= ' > NUL 2>&1';
        } else {
            // for Unix/Linux/Mac
            $command .= ' > /dev/null 2>&1';
        }

        exec($command);
    }

    private function showStartMessage($host, $port)
    {
        $currentDomain = "$host:$port";
        $projects = new Projects();
        $projectName = $projects->getProjectName($currentDomain);

        echo self::COLOR_RESET . "\nPreparing...";
        echo "\n\n";
        echo self::COLOR_GREEN . "  _____   _____     ____     _    _   _____   _____\n";
        echo self::COLOR_GREEN . " |_   _| / ____|   / __ \   | \  | | |_   _| / ____|\n";
        echo self::COLOR_GREEN . "   | |   | |      | |  | |  |  \ | |   | |   | |\n";
        echo self::COLOR_GREEN . "  _| |_  | |___   | |__| |  | | \  |  _| |_  | |____\n";
        echo self::COLOR_GREEN . " |_____| \_____|   \____/   |_|  \_| |_____| \_____|\n";
        echo self::COLOR_RESET . "\n\n Starting " . strtoupper($projectName) . " 🚀🚀🚀\n Application running on http://$host:$port\n\n";
        echo self::COLOR_RESET . "Press " . self::COLOR_YELLOW . "Ctrl + C" . self::COLOR_RESET . " to stop the development server.\n\n";
    }

    private function getPublicDirectory()
    {
        $rootDir = dirname(__DIR__, 8);
        return $rootDir . '/public'; // Path to public folder
    }

    private function showError($message)
    {
        echo self::COLOR_YELLOW . $message . "\n" . self::COLOR_RESET;
    }
}
