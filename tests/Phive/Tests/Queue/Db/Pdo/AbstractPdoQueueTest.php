<?php

namespace Phive\Tests\Queue\Db\Pdo;

use Phive\Queue\Db\Pdo\AbstractPdoQueue;
use Phive\Tests\Queue\AbstractPersistentQueueTest;

abstract class AbstractPdoQueueTest extends AbstractPersistentQueueTest
{
    /**
     * @dataProvider      throwRuntimeExceptionProvider
     * @expectedException \Phive\Queue\RuntimeException
     */
    public function testThrowRuntimeExceptionInSilentErrorMode(AbstractPdoQueue $queue, $method, array $args)
    {
        $queue->getConnection()->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);
        call_user_func_array(array($queue, $method), $args);
    }

    /**
     * @dataProvider      throwRuntimeExceptionProvider
     * @expectedException \Phive\Queue\RuntimeException
     */
    public function testThrowRuntimeExceptionInExceptionErrorMode(AbstractPdoQueue $queue, $method, array $args)
    {
        $queue->getConnection()->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        call_user_func_array(array($queue, $method), $args);
    }

    public function throwRuntimeExceptionProvider()
    {
        $handler = static::createHandler();
        $options = $handler->getOptions();
        $options['table_name'] = uniqid('non_existing_table_name_');

        $handler = new PdoHandler($options);
        $queue = $handler->createQueue();

        return array(
            array($queue, 'push',  array('item')),
            array($queue, 'pop',   array()),
            array($queue, 'slice', array(0, 1)),
            array($queue, 'count', array()),
            array($queue, 'clear', array()),
        );
    }
}
