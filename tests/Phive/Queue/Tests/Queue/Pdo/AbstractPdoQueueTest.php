<?php

namespace Phive\Queue\Tests\Queue\Pdo;

use Phive\Queue\Queue\Pdo\AbstractPdoQueue;
use Phive\Queue\Tests\Handler\PdoHandler;
use Phive\Queue\Tests\Queue\AbstractPersistentQueueTest;

abstract class AbstractPdoQueueTest extends AbstractPersistentQueueTest
{
    /**
     * @dataProvider      throwRuntimeExceptionProvider
     * @expectedException \Phive\Queue\Exception\RuntimeException
     */
    public function testThrowRuntimeExceptionInSilentErrorMode(AbstractPdoQueue $queue, $method, array $args)
    {
        $queue->getConnection()->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);
        call_user_func_array(array($queue, $method), $args);
    }

    /**
     * @dataProvider      throwRuntimeExceptionProvider
     * @expectedException \Phive\Queue\Exception\RuntimeException
     */
    public function testThrowRuntimeExceptionInExceptionErrorMode(AbstractPdoQueue $queue, $method, array $args)
    {
        $queue->getConnection()->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        call_user_func_array(array($queue, $method), $args);
    }

    public function throwRuntimeExceptionProvider()
    {
        $options = self::getHandler()->getOptions();
        $options['table_name'] = uniqid('non_existing_table_name_');

        $handler = new PdoHandler($options);
        $queue = $handler->createQueue();

        return array(
            array($queue, 'push',  array('item')),
            array($queue, 'pop',   array()),
            array($queue, 'count', array()),
            array($queue, 'clear', array()),
        );
    }
}
