<?php

namespace Phive\Queue\Tests\Handler;

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

        /*
        $driverName = strstr($this->getOption('dsn'), ':', true);
        if (!extension_loaded('pdo_'.$driverName)) {
            throw new \RuntimeException(sprintf('The "pdo_%s" extension is not loaded.', $driverName));
        }
        */

        parent::__construct($options);
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

    public function clear()
    {
        if (false === $this->pdo->exec('DELETE FROM '.$this->getOption('table_name'))) {
            $err = $this->pdo->errorInfo();
            throw new \RuntimeException($err[2]);
        }
    }

    public function execSqlFile($file)
    {
        $content = file_get_contents($file);
        $content = str_replace('{{table_name}}', $this->getOption('table_name'), $content);

        $statements = explode(';', $content);

        $this->pdo->beginTransaction();
        try {
            foreach ($statements as $statement) {
                $statement = trim($statement);

                // skip empty lines and comments
                if (!$statement || 0 === strpos($statement, '--')) {
                    continue;
                }

                if (false === $this->pdo->exec($statement)) {
                    $err = $this->pdo->errorInfo();
                    throw new \RuntimeException(
                        $err[2] ?: sprintf('Unable to execute the statement "%s".', $statement)
                    );
                }
            }
            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
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
        return '\\Phive\\Queue\\Queue\\Pdo\\'.ucfirst($this->driverName).'Queue';
    }
}
