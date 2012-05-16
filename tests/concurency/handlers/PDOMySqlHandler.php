<?php

use Phive\Queue\Db\PDO\PDOMySqlQueue;

class PDOMySqlHandler extends AbstractHandler
{
    protected static $conn;

    public function prepare()
    {
        self::$conn->exec('DROP TABLE IF EXISTS phive_queue');
        self::$conn->exec('CREATE TABLE phive_queue(id SERIAL, eta integer NOT NULL, item text NOT NULL) ENGINE=InnoDB');
    }

    public function shutdown()
    {
        self::$conn->exec('DROP TABLE IF EXISTS phive_queue');
        self::$conn = null;
    }

    protected function setup()
    {
        self::$conn = new \PDO('mysql:dbname=phive_tests', 'root');
    }

    protected function createQueue()
    {
        return new PDOMySqlQueue(self::$conn, 'phive_queue');
    }
}