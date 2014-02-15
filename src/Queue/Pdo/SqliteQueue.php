<?php

namespace Phive\Queue\Queue\Pdo;

use Phive\Queue\Exception\NoItemAvailableException;

class SqliteQueue extends AbstractPdoQueue
{
    /**
     * {@inheritdoc}
     */
    public function pop()
    {
        $sql = 'SELECT id, item FROM '.$this->tableName
            .' WHERE eta <= '.time().' ORDER BY eta LIMIT 1';

        $this->conn->exec('BEGIN IMMEDIATE');

        try {
            $stmt = $this->conn->query($sql);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            if ($row) {
                $sql = 'DELETE FROM '.$this->tableName.' WHERE id = '.(int) $row['id'];
                $this->conn->exec($sql);
            }

            $this->conn->exec('COMMIT');
        } catch (\Exception $e) {
            $this->conn->exec('ROLLBACK');
            throw $e;
        }

        if ($row) {
            return $row['item'];
        }

        throw new NoItemAvailableException();
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        return $this->conn->exec('DELETE FROM '.$this->tableName);
    }

    public function getSupportedDrivers()
    {
        return ['sqlite'];
    }
}
