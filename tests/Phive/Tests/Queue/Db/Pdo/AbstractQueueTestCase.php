<?php

namespace Phive\Tests\Queue\Db\Pdo;

use Phive\Tests\Queue\AbstractQueueTestCase as BaseAbstractQueueTestCase;

abstract class AbstractQueueTestCase extends BaseAbstractQueueTestCase
{
    /**
     * @var string
     */
    protected static $tableName = 'queue';

    /**
     * @var \PDO
     */
    protected static $conn;

    public static function setUpBeforeClass()
    {
        $driverName = static::getDriverName();

        if (!class_exists('PDO') || !in_array($driverName, \PDO::getAvailableDrivers())) {
            return;
        }

        parent::setUpBeforeClass();

        static::$conn = static::createConnection();
        //static::$conn->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
        //static::$conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        static::$conn->exec('DROP TABLE IF EXISTS '.static::$tableName);

        $sqlFile = __DIR__.'/../Fixtures/sql/'.$driverName.'.sql';
        static::execSqlFile($sqlFile, static::$conn);
    }

    public function setUp()
    {
        if (!static::$conn) {
            $this->markTestSkipped(sprintf(
                '%s requires %s PDO driver support in your environment.',
                $this->getQueueClassName(),
                static::getDriverName()
            ));
        }

        parent::setUp();

        static::$conn->exec('DELETE FROM '.static::$tableName);
        //'TRUNCATE TABLE queue RESTART IDENTITY'
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        if (static::$conn) {
            static::$conn->exec('DROP TABLE IF EXISTS '.static::$tableName);
            static::$conn = null;
        }
    }

    public function testPdoThrowsExceptionOnError()
    {
        $tableName = static::$tableName;

        static::$tableName = 'non_existing_table';
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

    public static function execSqlFile($file, \PDO $conn)
    {
        $statements = file($file);

        foreach ($statements as $statement) {
            if (false === $result = $conn->exec($statement)) {
                $err = $this->conn->errorInfo();
                throw new \RuntimeException($err[2]);
            }
        }
    }

    /**
     * @return \Phive\Queue\QueueInterface
     */
    protected function createQueue()
    {
        $queueClassName = $this->getQueueClassName();

        return new $queueClassName(static::$conn, static::$tableName);
    }

    /**
     * @return string
     */
    protected function getQueueClassName()
    {
        return '\\Phive\\Queue\\Db\\Pdo\\'.ucfirst(static::getDriverName()).'Queue';
    }

    /**
     * @return string
     */
    abstract protected static function getDriverName();

    /**
     * @return \PDO
     */
    abstract protected static function createConnection();
}
