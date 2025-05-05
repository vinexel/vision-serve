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

use PDO;
use PDOException;

class QueryBuilder
{
    private $conn;
    private $stmt;

    public function __construct(DatabaseConnection $dbConnection)
    {
        $this->conn = $dbConnection->getConnection();

        if (!$this->conn) {
            throw new PDOException("Database connection failed");
        }
    }

    public function query($query)
    {
        if (!$this->conn) {
            throw new PDOException("No 'query' PDO connection established");
        }
        $this->stmt = $this->conn->prepare($query);
        return $this;  // Enable method chaining
    }

    public function bind($param, $value, $type = null)
    {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }

        $this->stmt->bindValue($param, $value, $type);
        return $this;  // Enable method chaining
    }

    public function batchBind(array $params)
    {
        foreach ($params as $param => $value) {
            $this->bind($param, $value);
        }
        return $this;  // Enable method chaining
    }


    // public function execute()
    // {
    //     return $this->stmt->execute();
    // }
    public function execute()
    {
        try {
            return $this->stmt->execute();
        } catch (PDOException $e) {
            echo "Execution failed: " . $e->getMessage();
            return false;
        }
    }


    public function resultSet()
    {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function single()
    {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function rowCount()
    {
        return $this->stmt->rowCount();
    }

    public function prepare($query)
    {
        if (!$this->conn) {
            throw new PDOException("No 'prepare' PDO connection established");
        }
        return $this->conn->prepare($query);

        // Bind parameters if provided
        foreach ($params as $param => $value) {
            $this->bind($param, $value);
        }

        return $this;
    }
}
