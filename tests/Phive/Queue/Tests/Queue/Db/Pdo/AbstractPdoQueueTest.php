<?php

namespace Phive\Queue\Tests\Queue\Db\Pdo;

use Phive\Queue\Queue\Db\Pdo\AbstractPdoQueue;
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
        call_user_func_array([$queue, $method], $args);
    }

    /**
     * @dataProvider      throwRuntimeExceptionProvider
     * @expectedException \Phive\Queue\Exception\RuntimeException
     */
    public function testThrowRuntimeExceptionInExceptionErrorMode(AbstractPdoQueue $queue, $method, array $args)
    {
        $queue->getConnection()->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        call_user_func_array([$queue, $method], $args);
    }

    public function throwRuntimeExceptionProvider()
    {
        $handler = static::createHandler();
        $options = $handler->getOptions();
        $options['table_name'] = uniqid('non_existing_table_name_');

        $handler = new PdoHandler($options);
        $queue = $handler->createQueue();

        return [
            [$queue, 'push',  ['item']],
            [$queue, 'pop',   []],
            [$queue, 'count', []],
            [$queue, 'clear', []],
        ];
    }
}
