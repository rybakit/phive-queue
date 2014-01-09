<?php

namespace Phive\Queue\Queue\Pdo;

use Phive\Queue\Exception\NoItemException;

class MysqlQueue extends AbstractPdoQueue
{
    /**
     * {@inheritdoc}
     */
    public function pop()
    {
        $sql = 'SELECT id, item FROM '.$this->tableName
            .' WHERE eta <= '.time().' ORDER BY eta LIMIT 1 FOR UPDATE';

        $this->conn->beginTransaction();

        try {
            $stmt = $this->conn->query($sql);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            if ($row) {
                $sql = 'DELETE FROM '.$this->tableName.' WHERE id = '.(int) $row['id'];
                $this->conn->exec($sql);
            }

            $this->conn->commit();
        } catch (\Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }

        if ($row) {
            return $row['item'];
        }

        throw new NoItemException();
    }

    public function getSupportedDrivers()
    {
        return array('mysql');
    }
}
