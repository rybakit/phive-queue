<?php

namespace Phive\Queue\Queue\Pdo;

use Phive\Queue\Exception\NoItemAvailableException;

class GenericPdoQueue extends AbstractPdoQueue
{
    /**
     * @var array
     */
    protected static $popSqls = [
        'mysql'     => 'CALL %s(%d)',
        'pgsql'     => 'SELECT item FROM %s(%d)',
        'informix'  => 'EXECUTE PROCEDURE %s(%d)',
        'cubrid'    => 'CALL %s(%d)',
    ];

    /**
     * @var string
     */
    private $routineName;

    public function __construct(\PDO $conn, $tableName, $routineName = null)
    {
        parent::__construct($conn, $tableName);

        $this->routineName = $routineName ?: $this->tableName.'_pop';
    }

    /**
     * {@inheritdoc}
     */
    public function pop()
    {
        $stmt = $this->conn->query($this->getPopSql());
        $result = $stmt->fetchColumn();
        $stmt->closeCursor();

        if (false === $result) {
            throw new NoItemAvailableException();
        }

        return $result;
    }

    public function getSupportedDrivers()
    {
        return array_keys(static::$popSqls);
    }

    protected function getPopSql()
    {
        return sprintf(
            static::$popSqls[$this->conn->getAttribute(\PDO::ATTR_DRIVER_NAME)],
            $this->routineName,
            time()
        );
    }
}
