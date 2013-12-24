<?php

namespace Phive\Queue\Queue\Pdo;

use Phive\Queue\Exception\NoItemException;

class SqliteQueue extends AbstractPdoQueue
{
    /**
     * {@inheritdoc}
     */
    public function pop()
    {
        $sql = 'SELECT id, item FROM '.$this->tableName
            .' WHERE eta <= '.time().' ORDER BY eta LIMIT 1';

        $this->exec('BEGIN IMMEDIATE');

        try {
            $stmt = $this->query($sql);
            $row = $stmt->fetch();
            $stmt->closeCursor();

            if ($row) {
                $sql = 'DELETE FROM '.$this->tableName.' WHERE id = '.(int) $row['id'];
                $this->exec($sql);
            }

            $this->exec('COMMIT');
        } catch (\Exception $e) {
            $this->exec('ROLLBACK');
            throw $e;
        }

        if ($row) {
            return $row['item'];
        }

        throw new NoItemException();
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        return $this->exec('DELETE FROM '.$this->tableName);
    }

    public function getSupportedDrivers()
    {
        return array('sqlite');
    }
}
