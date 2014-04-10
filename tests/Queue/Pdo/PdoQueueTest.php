<?php

namespace Phive\Queue\Tests\Queue\Pdo;

use Phive\Queue\Tests\Queue\PersistenceTrait;
use Phive\Queue\Tests\Queue\QueueTest;

abstract class PdoQueueTest extends QueueTest
{
    use PersistenceTrait;

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testWrongErrorMode()
    {
        $handler = self::getHandler();

        $pdo = new \PDO(
            $handler->getOption('dsn'),
            $handler->getOption('username'),
            $handler->getOption('password')
        );
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);

        $class = $handler->getQueueClass();
        new $class($pdo, $handler->getOption('table_name'));
    }
}
