<?php

namespace Phive\Queue\Db\PDO;

class SQLitePDOQueue extends AbstractPDOQueue
{
    /**
     * @var \PDOStatement
     */
    protected $popSelectStatement;

    /**
     * @var \PDOStatement
     */
    protected $popDeleteStatement;

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
        if (!$this->popSelectStatement) {
            $sql = 'SELECT id, item FROM '.$this->tableName.' WHERE eta <= :eta ORDER BY eta, id LIMIT 1';
            $this->popSelectStatement = $this->prepareStatement($sql);
        }

        $this->popSelectStatement->bindValue(':eta', time(), \PDO::PARAM_INT);

        $this->execute('BEGIN IMMEDIATE');

        try {
            $this->executeStatement($this->popSelectStatement);

            if ($row = $this->popSelectStatement->fetch()) {
                $this->popSelectStatement->closeCursor();

                if (!$this->popDeleteStatement) {
                    $sql = 'DELETE FROM '.$this->tableName.' WHERE id = :id';
                    $this->popDeleteStatement = $this->prepareStatement($sql);
                }

                $this->popDeleteStatement->bindValue(':id', $row['id'], \PDO::PARAM_INT);
                $this->executeStatement($this->popDeleteStatement);
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
