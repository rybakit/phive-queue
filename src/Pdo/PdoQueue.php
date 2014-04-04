<?php

namespace Phive\Queue\Pdo;

use Phive\Queue as Q;
use Phive\Queue\Queue;

abstract class PdoQueue implements Queue
{
    /**
     * @var \PDO
     */
    protected $conn;

    /**
     * @var string
     */
    protected $tableName;

    public function __construct(\PDO $conn, $tableName)
    {
        if (\PDO::ERRMODE_EXCEPTION !== $conn->getAttribute(\PDO::ATTR_ERRMODE)) {
            throw new \InvalidArgumentException(sprintf('"%s" requires PDO error mode attribute be set to throw exceptions.', get_class($this)));
        }

        $supportedDrivers = (array) $this->getSupportedDrivers();
        $driver = $conn->getAttribute(\PDO::ATTR_DRIVER_NAME);

        if (!in_array($driver, $supportedDrivers)) {
            throw new \InvalidArgumentException(sprintf('PDO driver "%s" is unsupported by "%s".', $driver, get_class($this)));
        }

        $this->conn = $conn;
        $this->tableName = (string) $tableName;
    }

    public function getConnection()
    {
        return $this->conn;
    }

    /**
     * {@inheritdoc}
     */
    public function push($item, $eta = null)
    {
        $sql = sprintf('INSERT INTO %s (eta, item) VALUES (%d, %s)',
            $this->tableName,
            Q\normalize_eta($eta),
            $this->conn->quote($item)
        );

        $this->conn->exec($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        $stmt = $this->conn->query('SELECT COUNT(*) FROM '.$this->tableName);
        $result = $stmt->fetchColumn();
        $stmt->closeCursor();

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->conn->exec('DELETE FROM '.$this->tableName);
    }

    /**
     * @return array
     */
    abstract public function getSupportedDrivers();
}
