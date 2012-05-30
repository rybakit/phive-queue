<?php

namespace Phive\Queue\Db\Pdo;

use Phive\Queue\AdvancedQueueInterface;
use Phive\Queue\AbstractQueue as BaseAbstractQueue;

abstract class AbstractQueue extends BaseAbstractQueue implements AdvancedQueueInterface
{
    /**
     * @var ConnectionWrapper
     */
    protected $conn;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * Constructor.
     *
     * @param \PDO   $conn
     * @param string $tableName
     */
    public function __construct(\PDO $conn, $tableName)
    {
        $this->conn = new ConnectionWrapper($conn);
        $this->tableName = (string) $tableName;
    }

    public function getConnection()
    {
        return $this->conn->getConnection();
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
        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(':eta', $eta, \PDO::PARAM_INT);
        $stmt->bindValue(':item', $item);
        $stmt->execute();
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

        $stmt = $this->conn->query($sql);
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
        $stmt = $this->conn->query($sql);

        $result = $stmt->fetchColumn();
        $stmt->closeCursor();

        return $result;
    }

    /**
     * @see AdvancedQueueInterface::clear()
     */
    public function clear()
    {
        $sql = 'TRUNCATE TABLE '.$this->tableName;

        return $this->conn->execute($sql);
    }
}
