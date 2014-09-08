<?php

namespace Phive\Queue\Tests\Queue\Pdo;

use Phive\Queue\NoItemAvailableException;
use Phive\Queue\Tests\Handler\PdoHandler;
use Phive\Queue\Tests\Queue\ConcurrencyTrait;
use Phive\Queue\Tests\Queue\PerformanceTrait;
use Phive\Queue\Tests\Queue\QueueTest;
use Phive\Queue\Tests\Queue\Types;
use Phive\Queue\Tests\Queue\UtilTrait;

abstract class PdoQueueTest extends QueueTest
{
    use ConcurrencyTrait;
    use PerformanceTrait;
    use UtilTrait;

    public function getUnsupportedItemTypes()
    {
        return [Types::TYPE_BINARY_STRING, Types::TYPE_ARRAY, Types::TYPE_OBJECT];
    }

    /**
     * @dataProvider provideItemsOfUnsupportedTypes
     * @expectedException \Exception
     * @expectedExceptionMessage /(expects parameter 1 to be string)|(Binary strings are not identical)/
     */
    public function testUnsupportedItemType($item, $type)
    {
        $this->queue->push($item);

        if (Types::TYPE_BINARY_STRING === $type && $item !== $this->queue->pop()) {
            throw new \Exception('Binary strings are not identical');
        }
    }

    /**
     * @dataProvider provideQueueInterfaceMethods
     */
    public function testThrowExceptionOnMalformedSql($method)
    {
        $options = self::getHandler()->getOptions();
        $options['table_name'] = uniqid('non_existing_table_name_');

        $handler = new PdoHandler($options);
        $queue = $handler->createQueue();

        try {
            $this->callQueueMethod($queue, $method);
        } catch (NoItemAvailableException $e) {
        } catch (\PDOException $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage PDO driver "unsupported_driver" is unsupported
     */
    public function testThrowExceptionOnUnsupportedDriver()
    {
        $pdo = new MockPdo();
        $pdo->driverName = 'unsupported_driver';

        $handler = self::getHandler();
        $class = $handler->getQueueClass();

        new $class($pdo, $handler->getOption('table_name'));
    }
}
