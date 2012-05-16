<?php

namespace Phive\Queue\Db\PDO;

use Phive\Queue\AdvancedQueueInterface;
use Phive\Queue\AbstractQueue;

class PDOMySqlQueue extends PDOQueue implements AdvancedQueueInterface
{
    /**
     * @see QueueInterface::pop()
     */
    public function pop()
    {
        $this->conn->beginTransaction();

        try {
            $sql = 'SELECT id, item FROM '.$this->tableName
                .' WHERE eta <= :eta ORDER BY eta, id LIMIT 1 FOR UPDATE';

            $stmt = $this->prepareStatement($sql);
            $stmt->bindValue(':eta', time(), \PDO::PARAM_INT);

            if (!$stmt->execute()) {
                $err = $stmt->errorInfo();
                throw new \RuntimeException($err[2]);
            }

            if ($row = $stmt->fetch()) {
                $sql = 'DELETE FROM '.$this->tableName.' WHERE id = :id';
                $stmt = $this->prepareStatement($sql);
                $stmt->bindValue(':id', $row['id'], \PDO::PARAM_INT);

                if (!$stmt->execute()) {
                    $err = $stmt->errorInfo();
                    throw new \RuntimeException($err[2]);
                }
            }

            $this->conn->commit();
        } catch (\Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }

        return $row ? $row['item'] : false;
    }
}