<?php

namespace Vinexel\Modules\Database;

use PDO;
use PDOException;

/**
 * Class QueryBuilder
 *
 * Provides a lightweight, chainable interface for executing SQL queries
 * using PDO within the Vinexel Framework. This class simplifies common
 * database operations such as query preparation, parameter binding,
 * execution, and result retrieval.
 *
 * Responsibilities:
 * - Prepare and execute SQL queries securely.
 * - Bind parameters (single or batch) with automatic type detection.
 * - Fetch multiple or single records in associative array format.
 * - Manage transactions (begin, commit, rollback).
 * - Access raw PDO connections and statements when needed.
 */
class QueryBuilder
{
    /**
     * Active PDO database connection instance.
     *
     * @var PDO
     */
    private $conn;

    /**
     * Prepared PDO statement.
     *
     * @var \PDOStatement|null
     */
    private $stmt;

    /**
     * Create a new QueryBuilder instance.
     *
     * @param DatabaseConnection $dbConnection A valid database connection wrapper.
     * @throws PDOException If the provided connection is invalid or unavailable.
     */
    public function __construct(DatabaseConnection $dbConnection)
    {
        $this->conn = $dbConnection->getConnection();

        if (!$this->conn) {
            throw new PDOException("Database connection failed");
        }
    }

    /**
     * Prepare an SQL query for execution.
     *
     * @param string $query The SQL query string to prepare.
     * @return $this
     * @throws PDOException If no PDO connection is established.
     */
    public function query($query)
    {
        if (!$this->conn) {
            throw new PDOException("No PDO connection established");
        }
        $this->stmt = $this->conn->prepare($query);
        return $this;
    }

    /**
     * Bind a single parameter to the prepared statement.
     *
     * Automatically determines the correct PDO parameter type based on the value.
     *
     * @param string|int $param Parameter placeholder (e.g., ':id' or 1).
     * @param mixed $value The value to bind to the parameter.
     * @param int|null $type Optional explicit PDO type constant.
     * @return $this
     */
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
        return $this;
    }

    /**
     * Bind multiple parameters in a single batch operation.
     *
     * @param array $params Associative or indexed array of parameters to bind.
     *                      Keys can be either positional or named placeholders.
     * @return $this
     */
    public function batchBind(array $params)
    {
        foreach ($params as $key => $value) {
            if (is_int($key)) {
                $this->bind($key + 1, $value);
            } else {
                $this->bind($key, $value);
            }
        }
        return $this;
    }

    /**
     * Execute the prepared statement.
     *
     * @param array|null $params Optional parameters for direct execution.
     * @return bool True on success, false otherwise.
     * @throws PDOException If execution fails.
     */
    public function execute($params = null)
    {
        try {
            return $this->stmt->execute($params);
        } catch (PDOException $e) {
            throw new PDOException("Execution failed: " . $e->getMessage());
        }
    }

    /**
     * Fetch all result rows as an associative array.
     *
     * Automatically executes the query if not already executed.
     *
     * @return array Returns an array of associative rows.
     */
    public function resultSet()
    {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetch a single row from the result set.
     *
     * @param int $fetchMode The PDO fetch mode (default: FETCH_ASSOC).
     * @return array|null Returns a single associative array or null if no result.
     */
    public function single(int $fetchMode = \PDO::FETCH_ASSOC): ?array
    {
        $this->execute();
        $result = $this->stmt->fetch($fetchMode);
        return $result !== false ? $result : null;
    }

    /**
     * Get the number of affected rows from the last executed query.
     *
     * @return int The number of affected rows.
     */
    public function rowCount()
    {
        return $this->stmt->rowCount();
    }

    /**
     * Begin a new database transaction.
     *
     * @return bool True on success.
     * @throws PDOException If no database connection is available.
     */
    public function beginTransaction()
    {
        if (!$this->conn) {
            throw new PDOException("Cannot begin transaction: No database connection");
        }
        return $this->conn->beginTransaction();
    }

    /**
     * Commit the current transaction.
     *
     * @return bool True on success.
     * @throws PDOException If no database connection is available.
     */
    public function commit()
    {
        if (!$this->conn) {
            throw new PDOException("Cannot commit transaction: No database connection");
        }
        return $this->conn->commit();
    }

    /**
     * Roll back the current transaction.
     *
     * @return bool True on success.
     * @throws PDOException If no database connection is available.
     */
    public function rollBack()
    {
        if (!$this->conn) {
            throw new PDOException("Cannot rollback transaction: No database connection");
        }
        return $this->conn->rollBack();
    }

    /**
     * Get the raw PDO connection instance.
     *
     * @return PDO The current PDO connection.
     */
    public function getConnection()
    {
        return $this->conn;
    }

    /**
     * Prepare a query manually (alias of query()).
     *
     * @param string $query SQL query to prepare.
     * @return $this
     * @throws PDOException If no PDO connection is available.
     */
    public function prepare($query)
    {
        if (!$this->conn) {
            throw new PDOException("No PDO connection available for prepare()");
        }
        $this->stmt = $this->conn->prepare($query);
        return $this;
    }

    /**
     * Get the current PDOStatement instance.
     *
     * @return \PDOStatement|null The prepared statement or null if none.
     */
    public function getStmt()
    {
        return $this->stmt;
    }

    /**
     * Fetch a single column value from the result set.
     *
     * @param int $column Column index (default: 0).
     * @return mixed The value of the requested column.
     */
    public function fetchColumn($column = 0)
    {
        $this->execute();
        return $this->stmt->fetchColumn($column);
    }

    /**
     * Close the cursor for the current PDO statement.
     *
     * Frees up connection resources for subsequent queries.
     *
     * @return void
     */
    public function closeCursor()
    {
        if ($this->stmt) {
            $this->stmt->closeCursor();
        }
    }
}
