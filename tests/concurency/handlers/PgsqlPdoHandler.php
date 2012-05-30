<?php

use Phive\Queue\Db\Pdo\PgsqlQueue;

class PgsqlPdoHandler extends AbstractHandler
{
    /**
     * @var \PDO
     */
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
        //self::$conn->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
    }

    protected function createQueue()
    {
        return new PgsqlQueue(self::$conn, 'queue');
    }
}
