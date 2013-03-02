<?php

namespace Phive\Queue\Db\Pdo;

use Phive\Queue\AbstractQueue;
use Phive\Queue\RuntimeException;

abstract class AbstractPdoQueue extends AbstractQueue
{
    /**
     * @var \PDO
     */
    protected $conn;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * Constructor.
     *
     * @param \PDO   $conn
     * @param string $tableName
     */
    public function __construct(\PDO $conn, $tableName)
    {
        $this->conn = $conn;
        $this->tableName = (string) $tableName;
    }

    public function getConnection()
    {
        return $this->conn;
    }

    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * {@inheritdoc}
     */
    public function push($item, $eta = null)
    {
        $sql = sprintf('INSERT INTO %s (eta, item) VALUES (%d, %s)',
            $this->tableName,
            $this->normalizeEta($eta),
            $this->conn->quote($item)
        );

        $this->exec($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function peek($limit = 1, $skip = 0)
    {
        $this->assertLimit($limit, $skip);

        $sql = 'SELECT item FROM '.$this->tableName
            .' WHERE eta <= '.time().' ORDER BY eta, id';

        if ($limit > 0) {
            $sql .= ' LIMIT '.(int) $limit;
        }
        if ($skip > 0) {
            $sql .= ' OFFSET '.(int) $skip;
        }

        $stmt = $this->query($sql);
        $stmt->setFetchMode(\PDO::FETCH_COLUMN, 0);

        /*
        return new CallbackIterator(new \IteratorIterator($stmt), function ($item) use ($stmt) {
            if (null === $item) {
                $stmt->closeCursor();
            }

            return $item;
        });
        */

        return new \IteratorIterator($stmt);
        //return new \NoRewindIterator(new \IteratorIterator($stmt));
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        $stmt = $this->query('SELECT COUNT(*) FROM '.$this->tableName);
        $result = $stmt->fetchColumn();
        $stmt->closeCursor();

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        return $this->exec('TRUNCATE TABLE '.$this->tableName);
    }

    /**
     * @param string $sql
     *
     * @return int
     */
    protected function exec($sql)
    {
        return $this->exceptional(function(\PDO $conn) use ($sql) {
            return $conn->exec($sql);
        });
    }

    /**
     * @param string $sql
     *
     * @return \PDOStatement
     */
    protected function query($sql)
    {
        return $this->exceptional(function(\PDO $conn) use ($sql) {
            return $conn->query($sql);
        });
    }

    /**
     * @param \Closure $func The function to execute.
     *
     * @return mixed
     *
     * @throws RuntimeException
     */
    protected function exceptional(\Closure $func)
    {
        try {
            $result = $func($this->conn);
        } catch (\PDOException $e) {
            // We can't pass PDOException's code which is
            // an alphanumeric string rather than an integer.
            // @link http://www.php.net/manual/en/class.pdoexception.php#95812
            $err = $this->conn->errorInfo();
            throw new RuntimeException($err[2], $err[1], $e);
        }

        if (false === $result) {
            $err = $this->conn->errorInfo();
            throw new RuntimeException($err[2], $err[1]);
        }

        return $result;
    }
}
