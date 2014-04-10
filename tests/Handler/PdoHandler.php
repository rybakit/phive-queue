<?php

namespace Phive\Queue\Tests\Handler;

class PdoHandler extends Handler
{
    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @var string
     */
    private $driverName;

    public function getQueueClass()
    {
        $prefix = 'sqlite' === $this->driverName ? 'Sqlite' : 'Generic';

        return '\\Phive\\Queue\\Pdo\\'.$prefix.'PdoQueue';
    }

    public function createQueue()
    {
        $class = $this->getQueueClass();

        return new $class($this->pdo, $this->getOption('table_name'));
    }

    public function reset()
    {
        $sqlDir = realpath(__DIR__.'/../../contrib/'.$this->driverName);

        foreach (glob($sqlDir.'/*.sql') as $file) {
            $sql = strtr(file_get_contents($file), [
                '{{table_name}}'    => $this->getOption('table_name'),
                '{{routine_name}}'  => $this->getOption('table_name').'_pop',
            ]);

            $this->pdo->exec($sql);
        }
    }

    public function clear()
    {
        $this->pdo->exec('DELETE FROM '.$this->getOption('table_name'));
    }

    protected function configure()
    {
        $this->pdo = new \PDO(
            $this->getOption('dsn'),
            $this->getOption('username'),
            $this->getOption('password')
        );
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->driverName = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);

        $this->configureDriver();
    }

    protected function configureDriver()
    {
        switch ($this->driverName) {
            case 'sqlite':
                $this->pdo->exec('PRAGMA journal_mode=WAL');
                break;
        }
    }
}
