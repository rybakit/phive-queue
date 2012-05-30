<?php

namespace Phive\Tests\Queue\Db\Pdo;

use Phive\Tests\Queue\AbstractQueueTest;
use Phive\Queue\Db\Pdo\MysqlQueue;

class MysqlQueueTest extends AbstractQueueTest
{
    /**
     * @var \PDO
     */
    protected static $conn;

    public static function setUpBeforeClass()
    {
        if (!class_exists('PDO') || !in_array('mysql', \PDO::getAvailableDrivers())) {
            return;
        }

        parent::setUpBeforeClass();

        self::$conn = self::createConnection();
        //self::$conn->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);

        self::$conn->exec('DROP TABLE IF EXISTS queue');
        self::$conn->exec('CREATE TABLE queue(id SERIAL, eta integer NOT NULL, item text NOT NULL) ENGINE=InnoDB');
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
            $this->markTestSkipped('MySqlPDOQueue requires mysql PDO driver support in your environment.');
        }

        parent::setUp();

        self::$conn->exec('TRUNCATE TABLE queue');
    }

    protected function createQueue()
    {
        return new MysqlQueue(self::$conn, 'queue');
    }

    protected static function createConnection()
    {
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s',
            isset($GLOBALS['db_my_host']) ? $GLOBALS['db_my_host'] : 'localhost',
            isset($GLOBALS['db_my_port']) ? $GLOBALS['db_my_port'] : '3306',
            isset($GLOBALS['db_my_db_name']) ? $GLOBALS['db_my_db_name'] : 'phive_tests'
        );

        $username = isset($GLOBALS['db_my_username']) ? $GLOBALS['db_my_username'] : 'root';
        $password = isset($GLOBALS['db_my_password']) ? $GLOBALS['db_my_password'] : '';

        return new \PDO($dsn, $username, $password);
    }
}
