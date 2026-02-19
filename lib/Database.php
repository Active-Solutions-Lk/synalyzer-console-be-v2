<?php
/**
 * Database Connection Class
 * PDO-based database wrapper with error handling
 */

class Database
{

    private $pdo;
    private $config;

    /**
     * Initialize database connection
     * 
     * @param array $config Database configuration
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->connect();
    }

    /**
     * Establish database connection
     */
    private function connect()
    {
        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $this->config['host'],
                $this->config['port'],
                $this->config['database'],
                $this->config['charset']
            );

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->pdo = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                $options
            );

        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            throw new Exception('Database connection failed');
        }
    }

    /**
     * Execute a SELECT query
     * 
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return array Result rows
     */
    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Query failed: ' . $e->getMessage());
            throw new Exception('Query execution failed');
        }
    }

    /**
     * Execute a SELECT query and return single row
     * 
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return array|null Result row or null
     */
    public function queryOne($sql, $params = [])
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (PDOException $e) {
            error_log('Query failed: ' . $e->getMessage());
            throw new Exception('Query execution failed');
        }
    }

    /**
     * Execute an INSERT, UPDATE, or DELETE query
     * 
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return int Number of affected rows
     */
    public function execute($sql, $params = [])
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log('Execute failed: ' . $e->getMessage());
            throw new Exception('Query execution failed');
        }
    }

    /**
     * Get last insert ID
     * 
     * @return int Last insert ID
     */
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Begin transaction
     */
    public function beginTransaction()
    {
        $this->pdo->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit()
    {
        $this->pdo->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback()
    {
        $this->pdo->rollBack();
    }

    /**
     * Get PDO instance for advanced operations
     * 
     * @return PDO
     */
    public function getPdo()
    {
        return $this->pdo;
    }
}
