<?php

namespace Phive\Queue\Queue\Pdo;

use Phive\Queue\Exception\NoItemException;

class PgsqlQueue extends AbstractPdoQueue
{
    /**
     * {@inheritdoc}
     *
     * @link http://stackoverflow.com/questions/6507475/job-queue-as-sql-table-with-multiple-consumers-postgresql
     */
    public function pop()
    {
        $sql = 'SELECT id FROM '.$this->tableName.' WHERE eta <= '.time().' ORDER BY eta LIMIT 1';
        $sql = 'DELETE FROM '.$this->tableName.' WHERE id = ('.$sql.') RETURNING id, item';

        $this->conn->beginTransaction();

        try {
            $this->conn->exec('LOCK TABLE '.$this->tableName.' IN EXCLUSIVE MODE');
            $stmt = $this->conn->query($sql);
            $this->conn->commit();
        } catch (\Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }

        $row = $stmt->fetch();
        $stmt->closeCursor();

        if ($row) {
            return $row['item'];
        }

        throw new NoItemException();
    }

    public function getSupportedDrivers()
    {
        return array('pgsql');
    }
}
