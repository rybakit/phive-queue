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
        if (!extension_loaded('pdo')) {
            throw new \RuntimeException('The "pdo" extension is not loaded.');
        }

        parent::__construct($options);

        $driverName = strstr($this->getOption('dsn'), ':', true);
        if (!extension_loaded('pdo_'.$driverName)) {
            throw new \RuntimeException(sprintf('The "pdo_%s" extension is not loaded.', $driverName));
        }

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

    public function execSqlFile($file)
    {
        $statements = file($file);

        foreach ($statements as $statement) {
            if (false === $result = $this->pdo->exec($statement)) {
                $err = $this->pdo->errorInfo();
                throw new \RuntimeException($err[2]);
            }
        }
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
}
