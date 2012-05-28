<?php

namespace Phive\Queue\Db\PDO;

use Phive\Queue\AdvancedQueueInterface;
use Phive\Queue\AbstractQueue;

abstract class AbstractPDOQueue extends AbstractQueue implements AdvancedQueueInterface
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
     * @var \PDOStatement
     */
    protected $insertStatement;

    /**
     * Constructor.
     *
     * @param \PDO   $conn
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

        if (!$this->insertStatement) {
            $sql = 'INSERT INTO '.$this->tableName.' (eta, item) VALUES (:eta, :item)';
            $this->insertStatement = $this->prepareStatement($sql);
        }

        $this->insertStatement->bindValue(':eta', $eta, \PDO::PARAM_INT);
        $this->insertStatement->bindValue(':item', $item);

        $this->executeStatement($this->insertStatement);
    }

    /**
     * @see AdvancedQueueInterface::peek()
     */
    public function peek($limit = 1, $skip = 0)
    {
        $this->assertLimit($limit, $skip);

        $eta = time();
        $sql = "SELECT item FROM $this->tableName WHERE eta <= $eta ORDER BY eta, id";

        if ($limit > 0) {
            $sql .= ' LIMIT '.(int) $limit;
        }
        if ($skip > 0) {
            $sql .= ' OFFSET '.(int) $skip;
        }

        $stmt = $this->query($sql);
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
        $stmt = $this->query($sql);

        return $stmt->fetchColumn();
    }

    /**
     * @see AdvancedQueueInterface::clear()
     */
    public function clear()
    {
        $sql = 'TRUNCATE TABLE '.$this->tableName;

        return $this->execute($sql);
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

    /**
     * @param \PDOStatement $stmt
     *
     * @throws \RuntimeException
     */
    protected function executeStatement(\PDOStatement $stmt)
    {
        if (!$stmt->execute()) {
            $err = $stmt->errorInfo();
            throw new \RuntimeException($err[2]);
        }
    }

    /**
     * @param string $sql
     *
     * @return int
     *
     * @throws \RuntimeException
     */
    protected function execute($sql)
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
    protected function query($sql)
    {
        if (false === $result = $this->conn->query($sql)) {
            $err = $this->conn->errorInfo();
            throw new \RuntimeException($err[2]);
        }

        return $result;
    }
}
