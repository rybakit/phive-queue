<?php

use Phive\Queue\Db\PDO\MySqlPDOQueue;

class MySqlPDOHandler extends AbstractHandler
{
    /**
     * @var \PDO
     */
    protected static $conn;

    public function prepare()
    {
        self::$conn->exec('DROP TABLE IF EXISTS queue');
        self::$conn->exec('CREATE TABLE queue(id SERIAL, eta integer NOT NULL, item text NOT NULL) ENGINE=InnoDB');
    }

    public function shutdown()
    {
        self::$conn->exec('DROP TABLE IF EXISTS queue');
        self::$conn = null;
    }

    protected function setup()
    {
        self::$conn = new \PDO('mysql:dbname=phive_tests', 'root');
        //self::$conn->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
    }

    protected function createQueue()
    {
        return new MySqlPDOQueue(self::$conn, 'queue');
    }
}
