<?php

namespace Phive\Queue\Db\Pdo;

class MysqlQueue extends AbstractQueue
{
    public function __construct(\PDO $conn, $tableName)
    {
        if ('mysql' != $conn->getAttribute(\PDO::ATTR_DRIVER_NAME)) {
            throw new \InvalidArgumentException('Invalid PDO driver specified.');
        }

        parent::__construct($conn, $tableName);
    }

    /**
     * {@inheritdoc}
     */
    public function pop()
    {
        $sql = 'SELECT id, item FROM '.$this->tableName
            .' WHERE eta <= :eta ORDER BY eta, id LIMIT 1 FOR UPDATE';

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':eta', time(), \PDO::PARAM_INT);

        $this->conn->beginTransaction();

        try {
            $stmt->execute();
            $row = $stmt->fetch();
            $stmt->closeCursor();

            if ($row) {
                $sql = 'DELETE FROM '.$this->tableName.' WHERE id = :id';

                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':id', $row['id'], \PDO::PARAM_INT);
                $stmt->execute();
            }

            $this->conn->commit();
        } catch (\Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }

        return $row ? $row['item'] : false;
    }
}
