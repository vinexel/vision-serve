<?php

/**
 * Vinexel Framework.
 *
 * @package Vision
 * @author Elwira Perdana
 * @copyright (c) PT Iconic Wira Niaga
 * @license MIT License
 */

namespace Vinexel\Modules\Debug;

use Vision\Modules\Config;

class Debugger
{
    /**
     * Supported log levels.
     */
    const LOG_LEVELS = ['INFO', 'WARNING', 'ERROR', 'DEBUG'];

    /**
     * Available framework modes.
     */
    const MODES = ['development', 'testing', 'production'];

    /**
     * Current execution mode.
     *
     * @var string
     */
    private static $mode;

    /**
     * Initialize the debugger based on the application mode.
     *
     * @param string $mode The current environment mode (development, testing, production)
     */
    public static function init($mode)
    {
        self::$mode = $mode;
        switch (self::$mode) {
            case 'development':
                ini_set('display_errors', '1');
                ini_set('display_startup_errors', '1');
                error_reporting(E_ALL);
                break;
            case 'testing':
                ini_set('display_errors', '1');
                ini_set('display_startup_errors', '0');
                error_reporting(E_ALL);
                break;
            case 'production':
                ini_set('display_errors', '0');
                ini_set('log_errors', '1');
                error_reporting(0);
                break;
        }
    }

    /**
     * Write a log message to the project log file.
     *
     * @param string $message The log message.
     * @param string $level The severity level.
     */
    public static function log($message, $level = 'INFO')
    {
        if (!in_array($level, self::LOG_LEVELS)) {
            $level = 'INFO';
        }

        $logFile = VISION_DIR
            . DIRECTORY_SEPARATOR
            . 'system'
            . DIRECTORY_SEPARATOR
            . 'storage'
            . DIRECTORY_SEPARATOR
            . 'logging'
            . DIRECTORY_SEPARATOR
            . PROJECT_NAME
            . DIRECTORY_SEPARATOR
            . PROJECT_NAME
            . '.log';

        if (!file_exists(dirname($logFile))) {
            mkdir(dirname($logFile), 0777, true);
        }

        $formattedMessage = sprintf("[%s] [%s]: %s\n", date('Y-m-d H:i:s'), $level, $message);

        // Clean up old ERROR logs (older than 30 minutes)
        if ($level === 'ERROR' && file_exists($logFile)) {
            $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $newLines = [];
            $now = time();

            foreach ($lines as $line) {
                if (preg_match('/^\[(.*?)\]/', $line, $matches)) {
                    $timestamp = strtotime($matches[1]);
                    if ($timestamp !== false && ($now - $timestamp) <= 1800) {
                        $newLines[] = $line;
                    }
                }
            }
            file_put_contents($logFile, implode("\n", $newLines) . "\n");
        }
        file_put_contents($logFile, $formattedMessage, FILE_APPEND);
    }

    /**
     * Log a throwable error or exception.
     *
     * @param \Throwable $throwable
     */
    public static function logError(\Throwable $throwable)
    {
        self::log(self::formatError($throwable), 'ERROR');
    }

    /**
     * Format throwable error for readable logging.
     *
     * @param \Throwable $throwable
     * @return string
     */
    public static function formatError(\Throwable $throwable)
    {
        return sprintf(
            "Error: %s\nFile: %s on line %d\nTrace:\n%s\n",
            $throwable->getMessage(),
            $throwable->getFile(),
            $throwable->getLine(),
            $throwable->getTraceAsString()
        );
    }

    /**
     * Render an exception in an elegant way based on the current mode.
     *
     * @param \Throwable $exception
     */
    public static function renderError(\Throwable $exception)
    {
        $filePath = realpath($exception->getFile());

        if (!$filePath) {
            echo "Invalid file path: " . $exception->getFile();
            exit;
        }

        $fileDirectory = dirname($filePath);

        $frameworkPath = realpath(dirname(__DIR__, 6));
        $relativePath = str_replace($frameworkPath . DIRECTORY_SEPARATOR, '', $fileDirectory);

        $pathElements = explode(DIRECTORY_SEPARATOR, $relativePath);

        $projectFolder = isset($pathElements[0]) ? $pathElements[0] : 'Unknown Project';

        // Retrieve class namespace dynamically
        if (!function_exists('Vinexel\Modules\Debug\getNamespaceAndClassFromFile')) {
            function getNamespaceAndClassFromFile($filePath)
            {
                $namespace = null;
                $class = null;
                $lines = file($filePath);
                foreach ($lines as $line) {

                    if (strpos($line, 'namespace ') === 0) {
                        $namespace = trim(str_replace(['namespace', ';'], '', $line));
                    }

                    if (preg_match('/\b(class|trait|interface)\s+(\w+)/', $line, $matches)) {
                        $class = $matches[2];
                        break;
                    }
                }
                return $namespace && $class ? $namespace . '\\' . $class : $class;
            }
        }

        $namespaceClass = getNamespaceAndClassFromFile($filePath);

        /**
         * Retrieve lines surrounding the error for code context.
         */
        function getCodeContext($filePath, $errorLine, $contextRange = 5)
        {
            $lines = file($filePath);
            $startLine = max(0, $errorLine - $contextRange - 1);
            $endLine = min(count($lines), $errorLine + $contextRange);

            return array_slice($lines, $startLine, $endLine - $startLine, true);
        }

        $requestDetails = [
            'url'       => $_SERVER['REQUEST_URI'] ?? '',
            'method'    => $_SERVER['REQUEST_METHOD'] ?? '',
            'headers'   => getallheaders(),
            'input'     => $_REQUEST,
            'server'    => $_SERVER,
        ];

        $sessionDetails = $_SESSION ?? [];

        $codeContext = getCodeContext($filePath, $exception->getLine());

        $executionStartTime = $_SERVER["REQUEST_TIME_FLOAT"] ?? microtime(true);
        $executionEndTime = microtime(true);
        $executionTime = $executionEndTime - $executionStartTime;
        $memoryUsage = memory_get_usage(true);

        $errorDetails = [
            'message'       => $exception->getMessage(),
            'file'          => basename($filePath),
            'namespace'     => $namespaceClass,
            'project'       => $projectFolder,
            'directory'     => $fileDirectory,
            'line'          => $exception->getLine(),
            'trace'         => $exception->getTrace(),
            'file_tree'     => $pathElements,
            'request'       => $requestDetails,
            'session'       => $sessionDetails,
            'context'       => $codeContext,
            'php_version'   => phpversion(),
            'framework_version' => VINEXEL_VERSION,
            'execution_time' => $executionTime,
            'memory_usage'  => $memoryUsage
        ];

        // Clean output buffer to prevent partial HTML
        if (ob_get_level()) {
            ob_end_clean();
        }
        ob_start();
        if (self::$mode === 'development' || self::$mode === 'testing') {
            include VISION_DIR . '/system/vendor/plugins/vinexel/vision-serve/core/' . ucfirst('fragments') . '/' . ucfirst('resources') . '/view/error_template.rapid';
        } elseif (self::$mode === 'production') {
            include VISION_DIR . '/system/vendor/plugins/vinexel/vision-serve/core/' . ucfirst('fragments') . '/' . ucfirst('resources') . '/view/404.rapid';
        }
        ob_end_flush();
        exit;
    }

    /**
     * Check if current mode is debug mode.
     *
     * @return bool
     */
    public static function isDebugMode()
    {
        return self::$mode === 'development';
    }
}
