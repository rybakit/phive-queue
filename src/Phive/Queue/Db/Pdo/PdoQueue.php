<?php

namespace Phive\Queue\Db\Pdo;

use Phive\Queue\AdvancedQueueInterface;
use Phive\Queue\AbstractQueue;

class PdoQueue extends AbstractQueue implements AdvancedQueueInterface
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
     * Constructor.
     *
     * @param \PDO $conn
     * @param string $tableName
     */
    public function __construct(\PDO $conn, $tableName)
    {
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
     * @see QueueInterface::push()
     */
    public function push($item, $eta = null)
    {
        $eta = $eta ? $this->normalizeEta($eta) : time();
        $sql = 'INSERT INTO '.$this->tableName.' (eta, item) VALUES (:eta, :item)';

        $stmt = $this->prepareStatement($sql);
        $stmt->bindValue(':eta', $eta);
        $stmt->bindValue(':item', $item);

        if (!$stmt->execute()) {
            $err = $stmt->errorInfo();
            throw new \RuntimeException($err[2]);
        }
    }

    /**
     * @see QueueInterface::pop()
     */
    public function pop()
    {
        $sql = 'SELECT item FROM '.$this->tableName.' WHERE eta <= :eta ORDER BY eta, id LIMIT 1';

        $stmt = $this->prepareStatement($sql);
        $stmt->bindValue(':eta', time());

        if (!$stmt->execute()) {
            $err = $stmt->errorInfo();
            throw new \RuntimeException($err[2]);
        }

        return $stmt->fetchColumn();
    }

    /**
     * @see AdvancedQueueInterface::peek()
     */
    public function peek($limit = 1, $skip = 0)
    {
        if ($limit <= 0) {
            // Parameter limit must either be -1 or a value greater than or equal 0
            throw new \OutOfRangeException('Parameter limit must be greater than 0.');
        }
        if ($skip < 0) {
            throw new \OutOfRangeException('Parameter skip must be greater than or equal 0.');
        }

        $sql = 'SELECT item FROM '.$this->tableName.' WHERE eta <= :eta ORDER BY eta, id';

        if ($limit) {
            $sql .= ' LIMIT '.(int) $limit;
        }
        if ($skip) {
            $sql .= ' OFFSET '.(int) $skip;
        }

        $stmt = $this->prepareStatement($sql);
        $stmt->bindValue(':eta', time());

        if (!$stmt->execute()) {
            $err = $stmt->errorInfo();
            throw new \RuntimeException($err[2]);
        }

        $stmt->setFetchMode(\PDO::FETCH_COLUMN, 0);

        return new \IteratorIterator($stmt);
        //return new \NoRewindIterator(new \IteratorIterator($stmt));
    }

    /**
     * @see AdvancedQueueInterface::count()
     */
    public function count()
    {
        $sql = 'SELECT COUNT(*) FROM '.$this->tableName;
        $stmt = $this->prepareStatement($sql);

        if (!$stmt->execute()) {
            $err = $stmt->errorInfo();
            throw new \RuntimeException($err[2]);
        }

        return $stmt->fetchColumn();
    }

    /**
     * @see AdvancedQueueInterface::clear()
     */
    public function clear()
    {
        $sql = 'TRUNCATE TABLE '.$this->tableName;
        $stmt = $this->prepareStatement($sql);

        if (!$stmt->execute()) {
            $err = $stmt->errorInfo();
            throw new \RuntimeException($err[2]);
        }
    }

    /**
     * @param string $sql
     *
     * @return \PDOStatement
     *
     * @throws \RuntimeException
     */
    protected function prepareStatement($sql)
    {
        try {
            $stmt = $this->conn->prepare($sql);
        } catch (\Exception $e) {
            $stmt = false;
        }

        if (false === $stmt) {
            throw new \RuntimeException('The database cannot successfully prepare the statement.');
        }

        return $stmt;
    }
}