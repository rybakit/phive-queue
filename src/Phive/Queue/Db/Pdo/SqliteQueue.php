<?php

namespace Phive\Queue\Db\Pdo;

class SqliteQueue extends AbstractPdoQueue
{
    public function __construct(\PDO $conn, $tableName)
    {
        if ('sqlite' != $conn->getAttribute(\PDO::ATTR_DRIVER_NAME)) {
            throw new \InvalidArgumentException(sprintf('%s expects "sqlite" PDO driver, "%s" given.',
                __CLASS__, $conn->getAttribute(\PDO::ATTR_DRIVER_NAME)
            ));
        }

        parent::__construct($conn, $tableName);
    }

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

        return $row ? $row['item'] : false;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        return $this->exec('DELETE FROM '.$this->tableName);
    }
}
