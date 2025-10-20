<?php

/**
 * Vinexel Framework
 *
 * @package Vision
 * @author Elwira Perdana
 * @copyright (c) PT Iconic Wira Niaga
 * @license MIT License
 */

namespace Vinexel\Modules\Database;

use Vision\Modules\Config;
use PDO;
use PDOException;

/**
 * Class DatabaseConnection
 *
 * Provides a secure and persistent PDO-based database connection
 * for the Vinexel Framework. It dynamically loads configuration
 * values from the Config module or from environment constants as fallback.
 *
 * Responsibilities:
 * - Establish and manage a single PDO connection.
 * - Automatically handle connection errors.
 * - Offer a clean API to access and close database connections.
 */
class DatabaseConnection
{
    /**
     * Active PDO database connection instance.
     *
     * @var PDO|null
     */
    private $conn;

    /**
     * Create a new database connection.
     *
     * The constructor automatically attempts to establish a connection
     * using environment or configuration values. It will throw a
     * PDOException if configuration is missing or invalid.
     *
     * @throws PDOException
     */
    public function __construct()
    {
        // Retrieve configuration from Config or fallback to global constants.
        $host = Config::get('DB_HOST') ?? DB_HOST;
        $dbname = Config::get('DB_NAME') ?? DB_NAME;
        $username = Config::get('DB_USERNAME') ?? DB_USERNAME;
        $password = Config::get('DB_PASSWORD') ?? DB_PASSWORD;

        if (empty($host) || empty($dbname) || empty($username)) {
            throw new PDOException('Database configuration is not properly set.');
        }

        $dsn = "mysql:host=$host;dbname=$dbname";

        $options = [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ];

        try {
            $this->conn = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Get the current active PDO connection.
     *
     * If the connection is already established, it will be returned;
     * otherwise, null is returned.
     *
     * @return PDO|null The PDO connection instance or null if not available.
     */
    public function getConnection()
    {
        if ($this->conn instanceof PDO) {
            return $this->conn;
        }

        return null;
    }

    /**
     * Close the current database connection.
     *
     * Setting the PDO object to null releases the database connection
     * resources and ensures a clean shutdown.
     *
     * @return void
     */
    public function closeConnection()
    {
        $this->conn = null;
    }
}
