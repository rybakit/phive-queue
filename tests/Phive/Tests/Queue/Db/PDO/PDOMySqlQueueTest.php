<?php

namespace Phive\Tests\Queue\Db\PDO;

use Phive\Tests\Queue\AbstractQueueTest;
use Phive\Queue\Db\PDO\PDOMySqlQueue;

class PDOMySqlQueueTest extends AbstractQueueTest
{
    /**
     * @var \PDO
     */
    protected static $conn;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$conn = self::createConnection();
        self::$conn->exec('DROP TABLE IF EXISTS phive_queue');
        self::$conn->exec('CREATE TABLE phive_queue(id SERIAL, eta integer NOT NULL, item text NOT NULL) ENGINE=InnoDB');
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        self::$conn->exec('DROP TABLE IF EXISTS phive_queue');
        self::$conn = null;
    }

    public function setUp()
    {
        parent::setUp();

        self::$conn->exec('TRUNCATE phive_queue');
    }

    protected function createQueue()
    {
        return new PDOMySqlQueue(self::$conn, 'phive_queue');
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

        return new \Pdo($dsn, $username, $password);
    }
}