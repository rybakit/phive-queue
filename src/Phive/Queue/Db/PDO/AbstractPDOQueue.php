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
        $sql = 'INSERT INTO '.$this->tableName.' (eta, item) VALUES (:eta, :item)';

        $this->execute($sql, array(
            'eta'   => $eta,
            'item'  => $item,
        ));
    }

    /**
     * @see AdvancedQueueInterface::peek()
     */
    public function peek($limit = 1, $skip = 0)
    {
        $this->assertLimit($limit, $skip);

        $sql = 'SELECT item FROM '.$this->tableName.' WHERE eta <= :eta ORDER BY eta, id';

        if ($limit > 0) {
            $sql .= ' LIMIT '.(int) $limit;
        }
        if ($skip) {
            $sql .= ' OFFSET '.(int) $skip;
        }

        $stmt = $this->execute($sql, array('eta' => time()));
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
        $stmt = $this->execute($sql);

        return $stmt->fetchColumn();
    }

    /**
     * @see AdvancedQueueInterface::clear()
     */
    public function clear()
    {
        $sql = 'TRUNCATE TABLE '.$this->tableName;
        $stmt = $this->execute($sql);

        //return $stmt->rowCount();
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
     * @param string $sql
     * @param array  $parameters
     *
     * @return \PDOStatement
     *
     * @throws \RuntimeException
     */
    protected function execute($sql, array $parameters = array())
    {
        $stmt = $this->prepareStatement($sql);

        foreach ($parameters as $key => $value) {
            $type = is_numeric($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
            $stmt->bindValue(':'.$key, $value, $type);
        }

        if (!$stmt->execute()) {
            $err = $stmt->errorInfo();
            throw new \RuntimeException($err[2]);
        }

        return $stmt;
    }
}
