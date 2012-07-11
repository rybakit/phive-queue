<?php

namespace Phive\Tests\Queue\Db\Pdo;

use Phive\Tests\Queue\AbstractHandler;

class PdoHandler extends AbstractHandler
{
    /**
     * @var \PDO
     */
    protected $pdo;

    /**
     * @var string
     */
    protected $driverName;

    public function __construct(array $options = array())
    {
        if (!class_exists('\PDO')) {
            throw new \RuntimeException(__CLASS__.' requires the php "pdo" extension.');
        }

        parent::__construct($options);

        $this->configure();
    }

    public function createQueue()
    {
        $queueClassName = $this->getQueueClassName();

        return new $queueClassName($this->pdo, $this->getOption('table_name'));
    }

    public function reset()
    {
        $sqlFile = __DIR__.'/../Fixtures/sql/'.$this->driverName.'.sql';

        $this->execSqlFile($sqlFile);
    }

    protected function configure()
    {
        $this->pdo = new \PDO(
            $this->getOption('dsn'),
            $this->getOption('username'),
            $this->getOption('password')
        );
        //$this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
        //$this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $this->driverName = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
    }

    protected function getQueueClassName()
    {
        return '\\Phive\\Queue\\Db\\Pdo\\'.ucfirst($this->driverName).'Queue';
    }

    protected function execSqlFile($file)
    {
        $statements = file($file);

        foreach ($statements as $statement) {
            if (false === $result = $this->pdo->exec($statement)) {
                $err = $this->pdo->errorInfo();
                throw new \RuntimeException($err[2]);
            }
        }
    }
}
