<?php

namespace Phive\Queue\Queue\Pdo;

use Phive\Queue\Exception\NoItemAvailableException;

class GenericPdoQueue extends AbstractPdoQueue
{
    protected static $popStatements = [
        'mysql' => 'CALL %s_pop(%d)',
        'pgsql' => 'SELECT item FROM %s_pop(%d)',
    ];

    /**
     * {@inheritdoc}
     */
    public function pop()
    {
        $stmt = $this->conn->query($this->getPopStatement());
        $result = $stmt->fetchColumn();
        $stmt->closeCursor();

        if (false === $result) {
            throw new NoItemAvailableException();
        }

        return $result;
    }

    public function getSupportedDrivers()
    {
        return array_keys(static::$popStatements);
    }

    protected function getPopStatement()
    {
        return sprintf(
            static::$popStatements[$this->conn->getAttribute(\PDO::ATTR_DRIVER_NAME)],
            $this->tableName,
            time()
        );
    }
}
