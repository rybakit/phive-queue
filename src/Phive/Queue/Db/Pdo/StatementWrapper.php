<?php

namespace Phive\Queue\Db\Pdo;

use Phive\Exception\RuntimeException;

class StatementWrapper
{
    /**
     * @var \PDOStatement
     */
    protected $statement;

    public function __construct(\PDOStatement $statement)
    {
        $this->statement = $statement;
    }

    /**
     * @return \PDOStatement
     */
    public function getStatement()
    {
        return $this->statement;
    }

    /**
     * @param mixed $parameter
     * @param mixed $value
     * @param int   $dataType
     *
     * @return bool
     */
    public function bindValue($parameter, $value, $dataType = \PDO::PARAM_STR)
    {
        return $this->statement->bindValue($parameter, $value, $dataType);
    }

    /**
     * @param array|null $parameters
     *
     * @return bool
     *
     * @throws RuntimeException
     */
    public function execute(array $parameters = null)
    {
        if (!$this->statement->execute($parameters)) {
            $err = $this->statement->errorInfo();
            throw new RuntimeException($err[2]);
        }

        return true;
    }

    /**
     * @param int $fetchStyle
     * @param int $cursorOrientation
     * @param int $cursorOffset
     *
     * @return mixed
     */
    public function fetch($fetchStyle = null, $cursorOrientation = null, $cursorOffset = null)
    {
        return $this->statement->fetch($fetchStyle, $cursorOrientation, $cursorOffset);
    }

    /**
     * @param int $columnNumber
     *
     * @return string
     */
    public function fetchColumn($columnNumber = null)
    {
        return $this->statement->fetchColumn($columnNumber);
    }

    /**
     * @return bool
     */
    public function closeCursor()
    {
        return $this->statement->closeCursor();
    }
}
