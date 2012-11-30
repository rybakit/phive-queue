<?php

namespace Phive\Tests\Queue\Db\Pdo;

use Phive\Queue\Db\Pdo\AbstractPdoQueue;
use Phive\RuntimeException;
use Phive\Tests\Queue\HandlerAwareQueueTest;

abstract class AbstractPdoQueueTest extends HandlerAwareQueueTest
{
    /**
     * @dataProvider        testThrowRuntimeExceptionProvider
     * @expectedException   \Phive\RuntimeException
     */
    public function testThrowRuntimeException(AbstractPdoQueue $queue, $method)
    {
        foreach (array(\PDO::ERRMODE_SILENT, \PDO::ERRMODE_EXCEPTION) as $mode) {
            $queue->getConnection()->setAttribute(\PDO::ATTR_ERRMODE, $mode);
            ('push' === $method) ? $queue->$method('item') : $queue->$method();
        }
    }

    public function testThrowRuntimeExceptionProvider()
    {
        $handler = static::createHandler();
        $options = $handler->getOptions();
        $options['table_name'] = uniqid('non_existing_table_name_');

        $handler = new PdoHandler($options);
        $queue = $handler->createQueue();

        return array(
            array($queue, 'push'),
            array($queue, 'pop'),
            array($queue, 'peek'),
            array($queue, 'count'),
            array($queue, 'clear'),
        );
    }
}
