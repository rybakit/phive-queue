<?php

namespace Phive\Queue\Queue\Pdo;

use Phive\Queue\Exception\NoItemAvailableException;

class SqlitePdoQueue extends AbstractPdoQueue
{
    /**
     * {@inheritdoc}
     */
    public function pop()
    {
        $sql = sprintf(
            'SELECT id, item FROM %s WHERE eta <= %d ORDER BY eta LIMIT 1',
            $this->tableName,
            time()
        );

        $this->conn->exec('BEGIN IMMEDIATE');

        try {
            $stmt = $this->conn->query($sql);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            if ($row) {
                $sql = sprintf('DELETE FROM %s WHERE id = %d', $this->tableName, $row['id']);
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
