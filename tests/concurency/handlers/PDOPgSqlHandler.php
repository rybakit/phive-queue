<?php

use Phive\Queue\Db\PDO\PDOPgSqlQueue;

class PDOPgSqlHandler extends AbstractHandler
{
    protected static $conn;

    public function prepare()
    {
        self::$conn->exec('DROP TABLE IF EXISTS queue');
        self::$conn->exec('CREATE TABLE queue(id SERIAL, eta integer NOT NULL, item text NOT NULL)');
    }

    public function shutdown()
    {
        self::$conn->exec('DROP TABLE IF EXISTS queue');
        self::$conn = null;
    }

    protected function setup()
    {
        self::$conn = new \PDO('pgsql:dbname=phive_tests', 'postgres');
    }

    protected function createQueue()
    {
        return new PDOPgSqlQueue(self::$conn, 'queue');
    }
}