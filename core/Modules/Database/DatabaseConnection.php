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

class DatabaseConnection
{
    private $conn;

    public function __construct()
    {
        // Mengambil konfigurasi dari Config
        $host = Config::get('DB_HOST');
        $dbname = Config::get('DB_NAME');
        $username = Config::get('DB_USERNAME');
        $password = Config::get('DB_PASSWORD');

        // Mengecek jika konfigurasi database kosong
        if (empty($host) || empty($dbname) || empty($username)) {
            throw new PDOException('Database configuration is not properly set.');
        }

        $dsn = "mysql:host=$host;dbname=$dbname";

        $options = [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ];

        // Menangani koneksi dengan try-catch
        try {
            $this->conn = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            // Logging kesalahan dapat diterapkan di sini
            die('Database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Mendapatkan koneksi database
     *
     * @return PDO|null
     */
    public function getConnection()
    {
        // Mengecek jika koneksi sudah ada
        if ($this->conn instanceof PDO) {
            return $this->conn;
        }

        return null;
    }

    /**
     * Menutup koneksi database
     */
    public function closeConnection()
    {
        $this->conn = null; // Menutup koneksi
    }
}
