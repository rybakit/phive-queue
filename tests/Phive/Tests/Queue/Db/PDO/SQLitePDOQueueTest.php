<?php

namespace Phive\Tests\Queue\Db\PDO;

use Phive\Tests\Queue\AbstractQueueTest;
use Phive\Queue\Db\PDO\SQLitePDOQueue;

class SQLitePDOQueueTest extends AbstractQueueTest
{
    /**
     * @var \PDO
     */
    protected static $conn;

    public static function setUpBeforeClass()
    {
        if (!class_exists('PDO') || !in_array('sqlite', \PDO::getAvailableDrivers())) {
            return;
        }

        parent::setUpBeforeClass();

        self::$conn = self::createConnection();
        //self::$conn->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);

        self::$conn->exec('DROP TABLE IF EXISTS queue');
        self::$conn->exec('CREATE TABLE queue(id INTEGER PRIMARY KEY AUTOINCREMENT, eta integer NOT NULL, item blob NOT NULL)');
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
            $this->markTestSkipped('SQLitePDOQueue requires sqlite PDO driver support in your environment.');
        }

        parent::setUp();

        self::$conn->exec('DELETE FROM queue');
    }

    protected function createQueue()
    {
        return new SQLitePDOQueue(self::$conn, 'queue');
    }

    protected static function createConnection()
    {
        $dsn = sprintf('sqlite:%s/phive_tests.sq3', sys_get_temp_dir());

        return new \PDO($dsn);
    }
}
