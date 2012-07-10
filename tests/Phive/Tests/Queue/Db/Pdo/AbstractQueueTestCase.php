<?php

namespace Phive\Tests\Queue\Db\Pdo;

use Phive\Tests\Queue\AbstractQueueTestCase as BaseAbstractQueueTestCase;

abstract class AbstractQueueTestCase extends BaseAbstractQueueTestCase
{
    /**
     * @var GenericQueueManager
     */
    protected static $manager;

    public static function setUpBeforeClass()
    {
        if (!class_exists('PDO') || !in_array(static::getDriverName(), \PDO::getAvailableDrivers())) {
            return;
        }

        parent::setUpBeforeClass();

        static::$manager = static::createManager();
    }

    public function setUp()
    {
        if (!static::$manager) {
            $this->markTestSkipped(sprintf(
                '%s requires %s PDO driver support in your environment.',
                get_class($this),
                static::getDriverName()
            ));
        }

        parent::setUp();

        static::$manager->reset();
    }

    /*
    public function testPdoThrowsExceptionOnError()
    {
        $tableName = static::$tableName;

        static::$tableName = uniqid('non_existing_table_name_');
        $queue = $this->createQueue();

        $conn = $queue->getConnection();
        $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);

        try {
            $queue->clear();
        } catch (\Exception $e) {
            static::$tableName = $tableName;
            $this->assertInstanceOf('Exception', $e, 'PDO throws an exception on error');
            return;
        }

        static::$tableName = $tableName;
        $this->fail('PDO throws an exception on error');
    }
    */

    /**
     * @return \Phive\Queue\QueueInterface
     *
     * @throws \LogicException
     */
    public function createQueue()
    {
        if (static::$manager) {
            return static::$manager->createQueue();
        }

        throw new \LogicException('RedisQueueManager is not initialized.');
    }

    /**
     * @return GenericQueueManager
     */
    protected static function createManager()
    {
        $prefix = 'db_pdo_'.static::getDriverName();

        return new GenericQueueManager(array(
            'dsn'           => $GLOBALS[$prefix.'_dsn'],
            'username'      => $GLOBALS[$prefix.'_username'],
            'password'      => $GLOBALS[$prefix.'_password'],
            'table_name'    => $GLOBALS[$prefix.'_table_name'],
        ));
    }

    /**
     * @return string
     */
    abstract protected static function getDriverName();
}
