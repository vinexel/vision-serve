<?php

/**
 * Vinexel Framework.
 *
 * @package Vision
 * @author Elwira Perdana
 * @copyright (c) PT Iconic Wira Niaga
 * @license MIT License
 */

namespace Vinexel\Modules\Commands\Helpers\DB;

use PDO;
use Vision\Modules\Config;

class Seeder
{
    public function runSeeders($projectName)
    {
        $seederPath = dirname(__DIR__, 10) . DIRECTORY_SEPARATOR . "app/{$projectName}/database/seeds/";

        if (!file_exists($seederPath)) {
            echo "\e[31mError: Seeder path not found for project {$projectName}.\n\e[0m";
            return;
        }

        // Load the .env file for the specific project
        $envFilePath = dirname(__DIR__, 10) . DIRECTORY_SEPARATOR . "app/{$projectName}/.env";
        Config::loadEnv($envFilePath);

        $dbHost = Config::get('DB_HOST');
        $dbName = Config::get('DB_NAME');
        $dbUser = Config::get('DB_USER');
        $dbPass = Config::get('DB_PASS');

        // Create PDO connection
        try {
            $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName}", $dbUser, $dbPass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            foreach (glob($seederPath . "*.php") as $seederFile) {
                require_once $seederFile;

                $classNameWithTimestamp = basename($seederFile, '.php');

                $parts = explode('_', $classNameWithTimestamp);
                $className = end($parts);

                $fullyQualifiedClassName = "$projectName\\Database\\Seeds\\{$className}";

                echo "Trying to run seeder: {$fullyQualifiedClassName}\n";

                if (class_exists($fullyQualifiedClassName)) {
                    $seeder = new $fullyQualifiedClassName();
                    $seeder->run($pdo);
                    echo "\e[32mSeeder {$className} ran successfully.\n\e[0m";
                } else {
                    echo "\e[31mError: Seeder class {$fullyQualifiedClassName} not found.\n\e[0m";
                }
            }
        } catch (\PDOException $e) {
            echo "\e[31mDatabase connection failed: " . $e->getMessage() . "\n\e[0m";
        }
    }

    public function rollbackSeeders($projectName)
    {
        $seederPath = dirname(__DIR__, 10) . DIRECTORY_SEPARATOR . "app/{$projectName}/database/seeds/";

        if (!file_exists($seederPath)) {
            echo "\e[31mError: Seeder path not found for project {$projectName}.\n\e[0m";
            return;
        }

        // Load the .env file for the specific project
        $envFilePath = dirname(__DIR__, 10) . DIRECTORY_SEPARATOR . "app/{$projectName}/.env";
        Config::loadEnv($envFilePath);

        $dbHost = Config::get('DB_HOST', 'localhost');
        $dbName = Config::get('DB_NAME', 'database');
        $dbUser = Config::get('DB_USER', 'root');
        $dbPass = Config::get('DB_PASS', '');

        // Create PDO connection
        try {
            $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName}", $dbUser, $dbPass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Reverse order for rollback
            foreach (array_reverse(glob($seederPath . "*.php")) as $seederFile) {
                require_once $seederFile;

                $classNameWithTimestamp = basename($seederFile, '.php');

                $parts = explode('_', $classNameWithTimestamp);
                $className = end($parts);

                $fullyQualifiedClassName = "$projectName\\Database\\Seeds\\{$className}";

                echo "Trying to rollback seeder: {$fullyQualifiedClassName}\n";

                if (class_exists($fullyQualifiedClassName)) {
                    $seeder = new $fullyQualifiedClassName();
                    $seeder->rollback($pdo);
                    echo "\e[32mSeeder {$className} rolled back successfully.\n\e[0m";
                } else {
                    echo "\e[31mError: Seeder class {$fullyQualifiedClassName} not found.\n\e[0m";
                }
            }
        } catch (\PDOException $e) {
            echo "\e[31mDatabase connection failed: " . $e->getMessage() . "\n\e[0m";
        }
    }
}
