<?php

namespace Phive\Tests\Queue\Db\Pdo;

use Phive\Tests\Queue\HandlerAwareQueueTestCase as BaseHandlerAwareQueueTestCase;

abstract class HandlerAwareQueueTestCase extends BaseHandlerAwareQueueTestCase
{
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
}
