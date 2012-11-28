<?php

namespace Phive\Tests\Queue\Db\Pdo;

use Phive\RuntimeException;
use Phive\Tests\Queue\HandlerAwareQueueTest;

abstract class AbstractPdoQueueTest extends HandlerAwareQueueTest
{
    public function testRuntimeExceptionThrowing()
    {
        $options = static::$handler->getOptions();
        $options['table_name'] = uniqid('non_existing_table_name_');

        $handler = new PdoHandler($options);
        $queue = $handler->createQueue();
        $conn = $queue->getConnection();

        $errorModes = array(
            \PDO::ERRMODE_SILENT,
            \PDO::ERRMODE_EXCEPTION,
        );

        foreach ($errorModes as $mode) {
            $conn->setAttribute(\PDO::ATTR_ERRMODE, $mode);
            foreach (array('push', 'pop', 'peek', 'clear', 'count') as $method) {
                try {
                    if ('push' === $method) {
                        $queue->$method('item');
                    } else {
                        $queue->$method();
                    }
                } catch (RuntimeException $e) {
                    continue;
                }

                $this->fail(get_class($queue).":$method() throws \\Phive\\RuntimeException on error.");
            }
        }

        $conn = null;
    }
}
