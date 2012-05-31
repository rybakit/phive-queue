<?php

namespace Phive\Queue\Db\Pdo;

class ConnectionWrapper
{
    /**
     * @var \PDO
     */
    protected $conn;

    /**
     * @var StatementWrapper[]
     */
    protected $preparedStatements = array();

    public function __construct(\PDO $conn)
    {
        $this->conn = $conn;
    }

    /**
     * @return \PDO
     */
    public function getConnection()
    {
        return $this->conn;
    }

    /**
     * @param string $sql
     *
     * @return int
     *
     * @throws \RuntimeException
     */
    public function execute($sql)
    {
        if (false === $result = $this->conn->exec($sql)) {
            $err = $this->conn->errorInfo();
            throw new \RuntimeException($err[2]);
        }

        return $result;
    }

    /**
     * @param string $sql
     *
     * @return \PDOStatement
     *
     * @throws \RuntimeException
     */
    public function query($sql)
    {
        if (false === $result = $this->conn->query($sql)) {
            $err = $this->conn->errorInfo();
            throw new \RuntimeException($err[2]);
        }

        return $result;
    }

    /**
     * @param string $sql
     * @param array  $driverOptions
     *
     * @return StatementWrapper
     *
     * @throws \RuntimeException
     */
    public function prepare($sql, array $driverOptions = array())
    {
        if (!isset($this->preparedStatements[$sql])) {
            try {
                $stmt = $this->conn->prepare($sql, $driverOptions);
            } catch (\Exception $e) {
                $stmt = false;
            }

            if (false === $stmt) {
                throw new \RuntimeException('The database cannot successfully prepare the statement.');
            }

            $this->preparedStatements[$sql] = new StatementWrapper($stmt);
        }

        return $this->preparedStatements[$sql];
    }

    /**
     * @return bool
     */
    public function beginTransaction()
    {
        return $this->conn->beginTransaction();
    }

    /**
     * @return bool
     */
    public function commit()
    {
        return $this->conn->commit();
    }

    /**
     * @return bool
     */
    public function rollBack()
    {
        return $this->conn->rollBack();
    }

    /**
     * Clears any stored prepared statements for this connection.
     */
    public function clearStatementCache()
    {
        $this->preparedStatements = array();
    }
}
