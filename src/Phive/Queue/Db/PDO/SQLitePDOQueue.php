<?php

namespace Phive\Queue\Db\PDO;

class SQLitePDOQueue extends AbstractPDOQueue
{
    public function __construct(\PDO $conn, $tableName)
    {
        if ('sqlite' != $conn->getAttribute(\PDO::ATTR_DRIVER_NAME)) {
            throw new \InvalidArgumentException('Invalid PDO driver specified.');
        }

        parent::__construct($conn, $tableName);
    }

    /**
     * @see QueueInterface::pop()
     */
    public function pop()
    {
        $sql = 'SELECT id, item FROM '.$this->tableName.' WHERE eta <= :eta ORDER BY eta, id LIMIT 1';

        $stmt = $this->prepareStatement($sql);
        $stmt->bindValue(':eta', time(), \PDO::PARAM_INT);

        $this->execute('BEGIN IMMEDIATE');

        try {
            $this->executeStatement($stmt);

            if ($row = $stmt->fetch()) {
                $stmt->closeCursor();

                $sql = 'DELETE FROM '.$this->tableName.' WHERE id = :id';

                $stmt = $this->prepareStatement($sql);
                $stmt->bindValue(':id', $row['id'], \PDO::PARAM_INT);

                $this->executeStatement($stmt);
            }

            $this->execute('COMMIT');
        } catch (\Exception $e) {
            $this->execute('ROLLBACK');
            throw $e;
        }

        return $row ? $row['item'] : false;
    }

    /**
     * @see QueueInterface::clear()
     */
    public function clear()
    {
        $sql = 'DELETE FROM '.$this->tableName;

        return $this->execute($sql);
    }
}
