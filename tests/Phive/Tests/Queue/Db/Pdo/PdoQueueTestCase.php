<?php

namespace Phive\Tests\Queue\Db\Pdo;

use Phive\Exception\RuntimeException;
use Phive\Tests\Queue\HandlerAwareQueueTestCase;

abstract class PdoQueueTestCase extends HandlerAwareQueueTestCase
{
    public function testPdoThrowsExceptionOnError()
    {
        $options = static::$handler->getOptions();
        $options['table_name'] = uniqid('non_existing_table_name_');

        $handler = new PdoHandler($options);
        $queue = $handler->createQueue();

        $conn = $queue->getConnection();
        $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);

        foreach (array('push', 'pop', 'peek', 'clear', 'count') as $method) {
            try {
                if ('push' === $method) {
                    $queue->$method('item');
                } else {
                    $queue->$method();
                }
            } catch (RuntimeException $e) {
                $this->assertInstanceOf('Exception', $e, 'PDO throws an exception on error');
                continue;
            }

            $this->fail('PDO throws an exception on error');
        }

        $conn = null;
    }
}
