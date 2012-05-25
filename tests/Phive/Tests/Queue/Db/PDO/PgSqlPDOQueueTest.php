<?php

namespace Phive\Tests\Queue\Db\PDO;

use Phive\Tests\Queue\AbstractQueueTest;
use Phive\Queue\Db\PDO\PgSqlPDOQueue;

class PDOPgSqlQueueTest extends AbstractQueueTest
{
    /**
     * @var \PDO
     */
    protected static $conn;

    public static function setUpBeforeClass()
    {
        if (!class_exists('PDO') || !in_array('pgsql', \PDO::getAvailableDrivers())) {
            return;
        }

        parent::setUpBeforeClass();

        self::$conn = self::createConnection();
        self::$conn->exec('DROP TABLE IF EXISTS queue');
        self::$conn->exec('CREATE TABLE queue(id SERIAL, eta integer NOT NULL, item text NOT NULL)');
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        if (self::$conn) {
            self::$conn->exec('DROP TABLE IF EXISTS queue');
            self::$conn = null;
        }
    }

    public function setUp()
    {
        if (!self::$conn) {
            $this->markTestSkipped('PgSqlPDOQueue requires pgsql PDO driver support in your environment.');
        }

        parent::setUp();

        self::$conn->exec('TRUNCATE TABLE queue RESTART IDENTITY');
    }

    protected function createQueue()
    {
        return new PgSqlPDOQueue(self::$conn, 'queue');
    }

    protected static function createConnection()
    {
        $dsn = sprintf('pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s',
            isset($GLOBALS['db_pg_host']) ? $GLOBALS['db_pg_host'] : 'localhost',
            isset($GLOBALS['db_pg_port']) ? $GLOBALS['db_pg_port'] : '5432',
            isset($GLOBALS['db_pg_db_name']) ? $GLOBALS['db_pg_db_name'] : 'phive_tests',
            isset($GLOBALS['db_pg_username']) ? $GLOBALS['db_pg_username'] : 'postgres',
            isset($GLOBALS['db_pg_password']) ? $GLOBALS['db_pg_password'] : ''
        );

        return new \PDO($dsn);
    }
}
