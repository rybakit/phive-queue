<?php

namespace Phive\Queue\Db\PDO;

use Phive\Queue\AdvancedQueueInterface;
use Phive\Queue\AbstractQueue;

class PDOMySqlQueue extends AbstractPDOQueue implements AdvancedQueueInterface
{
    public function __construct(\PDO $conn, $tableName)
    {
        if ('mysql' != $conn->getAttribute(\PDO::ATTR_DRIVER_NAME)) {
            throw new \InvalidArgumentException('Invalid PDO driver specified.');
        }

        parent::__construct($conn, $tableName);
    }

    /**
     * @see QueueInterface::pop()
     */
    public function pop()
    {
        $sql = 'SELECT id, item FROM '.$this->tableName
            .' WHERE eta <= :eta ORDER BY eta, id LIMIT 1 FOR UPDATE';

        $this->conn->beginTransaction();

        try {
            $stmt = $this->execute($sql, array('eta' => time()));

            if ($row = $stmt->fetch()) {
                $sql = 'DELETE FROM '.$this->tableName.' WHERE id = :id';
                $stmt = $this->execute($sql, array('id' => $row['id']));
            }

            $this->conn->commit();
        } catch (\Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }

        return $row ? $row['item'] : false;
    }
}