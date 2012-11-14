<?php

namespace Phive\Queue\Db\Pdo;

use Phive\Queue\AbstractQueue as BaseAbstractQueue;

abstract class AbstractQueue extends BaseAbstractQueue
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
        $this->conn = $this->createConnectionWrapper($conn);
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
     * {@inheritdoc}
     */
    public function push($item, $eta = null)
    {
        $eta = $this->normalizeEta($eta);

        $sql = 'INSERT INTO '.$this->tableName.' (eta, item) VALUES (:eta, :item)';
        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(':eta', $eta, \PDO::PARAM_INT);
        $stmt->bindValue(':item', $item);
        $stmt->execute();
    }

    /**
     * {@inheritdoc}
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

        /*
        return new CallbackIterator(new \IteratorIterator($stmt), function ($item) use ($stmt) {
            if (null === $item) {
                $stmt->closeCursor();
            }

            return $item;
        });
        */

        return new \IteratorIterator($stmt);
        //return new \NoRewindIterator(new \IteratorIterator($stmt));
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function clear()
    {
        $sql = 'TRUNCATE TABLE '.$this->tableName;

        return $this->conn->execute($sql);
    }

    /**
     * @param \PDO $conn
     *
     * @return ConnectionWrapper
     */
    protected function createConnectionWrapper(\PDO $conn)
    {
        return new ConnectionWrapper($conn);
    }
}
