<?php

namespace Phive\Queue\Queue\Pdo;

use Phive\Queue\Exception\NoItemException;
use Phive\Queue\Exception\InvalidArgumentException;

class MysqlQueue extends AbstractPdoQueue
{
    public function __construct(\PDO $conn, $tableName)
    {
        if ('mysql' != $conn->getAttribute(\PDO::ATTR_DRIVER_NAME)) {
            throw new InvalidArgumentException(sprintf('%s expects "mysql" PDO driver, "%s" given.',
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
            .' WHERE eta <= '.time().' ORDER BY eta LIMIT 1 FOR UPDATE';

        $this->conn->beginTransaction();

        try {
            $stmt = $this->query($sql);
            $row = $stmt->fetch();
            $stmt->closeCursor();

            if ($row) {
                $sql = 'DELETE FROM '.$this->tableName.' WHERE id = '.(int) $row['id'];
                $this->exec($sql);
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
}
