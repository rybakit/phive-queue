<?php

namespace Phive\Queue\Tests\Queue\Pdo;

use Phive\Queue\Tests\Queue\AbstractPersistentQueueTest;

abstract class AbstractPdoQueueTest extends AbstractPersistentQueueTest
{
    /**
     * @expectedException \Phive\Queue\Exception\InvalidArgumentException
     */
    public function testWrongErrorMode()
    {
        $handler = self::getHandler();

        $conn = new \PDO(
            $handler->getOption('dsn'),
            $handler->getOption('username'),
            $handler->getOption('password')
        );
        $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);

        $class = $handler->getQueueClass();
        new $class($conn, $handler->getOption('table_name'));
    }
}
