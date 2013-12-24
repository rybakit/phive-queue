<?php

namespace Phive\Queue\Queue\Pdo;

use Phive\Queue\Exception\InvalidArgumentException;
use Phive\Queue\Exception\RuntimeException;
use Phive\Queue\Queue\QueueInterface;
use Phive\Queue\QueueUtils;

abstract class AbstractPdoQueue implements QueueInterface
{
    /**
     * @var \PDO
     */
    protected $conn;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * @param \PDO   $conn
     * @param string $tableName
     *
     * @throws InvalidArgumentException
     */
    public function __construct(\PDO $conn, $tableName)
    {
        $supportedDrivers = (array) $this->getSupportedDrivers();
        $driver = $conn->getAttribute(\PDO::ATTR_DRIVER_NAME);

        if (!in_array($driver, $supportedDrivers)) {
            throw new InvalidArgumentException(sprintf('PDO driver "%s" is unsupported by "%s".', $driver, get_class($this)));
        }

        $this->conn = $conn;
        $this->tableName = (string) $tableName;
    }

    public function getConnection()
    {
        return $this->conn;
    }

    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * {@inheritdoc}
     */
    public function push($item, $eta = null)
    {
        $sql = sprintf('INSERT INTO %s (eta, item) VALUES (%d, %s)',
            $this->tableName,
            QueueUtils::normalizeEta($eta),
            $this->conn->quote($item)
        );

        $this->exec($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        $stmt = $this->query('SELECT COUNT(*) FROM '.$this->tableName);
        $result = $stmt->fetchColumn();
        $stmt->closeCursor();

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        return $this->exec('TRUNCATE TABLE '.$this->tableName);
    }

    /**
     * @param string $sql
     *
     * @return int
     */
    protected function exec($sql)
    {
        return $this->exceptional(function(\PDO $conn) use ($sql) {
            return $conn->exec($sql);
        });
    }

    /**
     * @param string $sql
     *
     * @return \PDOStatement
     */
    protected function query($sql)
    {
        return $this->exceptional(function(\PDO $conn) use ($sql) {
            return $conn->query($sql);
        });
    }

    /**
     * @param \Closure $func The function to execute.
     *
     * @return mixed
     *
     * @throws RuntimeException
     */
    protected function exceptional(\Closure $func)
    {
        try {
            $result = $func($this->conn);
        } catch (\PDOException $e) {
            // We can't pass PDOException's code which is
            // an alphanumeric string rather than an integer.
            // @link http://www.php.net/manual/en/class.pdoexception.php#95812
            $err = $this->conn->errorInfo();
            throw new RuntimeException($err[2], $err[1], $e);
        }

        if (false === $result) {
            $err = $this->conn->errorInfo();
            throw new RuntimeException($err[2], $err[1]);
        }

        return $result;
    }

    /**
     * @return array
     */
    abstract public function getSupportedDrivers();
}
