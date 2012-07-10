<?php

namespace Phive\Tests\Queue\Db\Pdo;

use Phive\Tests\Queue\AbstractQueueManager;

class GenericQueueManager extends AbstractQueueManager
{
    /**
     * @var \PDO
     */
    protected $pdo;

    public function createQueue()
    {
        $this->initPdo();

        $driverName = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
        $queueClassName = $this->getQueueClassName($driverName);

        return new $queueClassName($this->pdo, $this->getOption('table_name'));
    }

    public function reset()
    {
        $this->initPdo();

        $driverName = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
        $sqlFile = __DIR__.'/../Fixtures/sql/'.$driverName.'.sql';

        $this->execSqlFile($sqlFile);
    }

    protected function getQueueClassName($driverName)
    {
        return '\\Phive\\Queue\\Db\\Pdo\\'.ucfirst($driverName).'Queue';
    }

    protected function execSqlFile($file)
    {
        $this->initPdo();

        $statements = file($file);

        foreach ($statements as $statement) {
            if (false === $result = $this->pdo->exec($statement)) {
                $err = $this->pdo->errorInfo();
                throw new \RuntimeException($err[2]);
            }
        }
    }

    protected function initPdo()
    {
        if (!$this->pdo) {
            $this->pdo = new \PDO(
                $this->getOption('dsn'),
                $this->getOption('username'),
                $this->getOption('password')
            );
            //$this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
            //$this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }
    }
}
