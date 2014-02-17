<?php

namespace Phive\Queue\Tests\Queue\Pdo;

use Phive\Queue\Tests\Queue\AbstractQueueTest;
use Phive\Queue\Tests\Queue\PersistenceTrait;

abstract class AbstractPdoQueueTest extends AbstractQueueTest
{
    use PersistenceTrait;

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
