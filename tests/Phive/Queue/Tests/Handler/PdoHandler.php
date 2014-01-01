<?php

namespace Phive\Queue\Tests\Handler;

class PdoHandler extends AbstractHandler
{
    /**
     * @var \PDO
     */
    protected $conn;

    /**
     * @var string
     */
    protected $driverName;

    public function createQueue()
    {
        $class = '\\Phive\\Queue\\Queue\\Pdo\\'.ucfirst($this->driverName).'Queue';

        return new $class($this->conn, $this->getOption('table_name'));
    }

    public function reset()
    {
        $file = __DIR__.'/../Fixtures/sql/'.$this->driverName.'.sql';
        $sql = file_get_contents($file);
        $sql = str_replace('{{table_name}}', $this->getOption('table_name'), $sql);

        $this->conn->exec($sql);
    }

    public function clear()
    {
        $this->conn->exec('DELETE FROM '.$this->getOption('table_name'));
    }

    protected function configure()
    {
        $this->conn = new \PDO(
            $this->getOption('dsn'),
            $this->getOption('username'),
            $this->getOption('password')
        );
        $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->driverName = $this->conn->getAttribute(\PDO::ATTR_DRIVER_NAME);

        $this->configureDriver();
    }

    protected function configureDriver()
    {
        switch ($this->driverName) {
            case 'sqlite':
                $this->conn->exec('PRAGMA journal_mode=WAL');
                break;
        }
    }
}
