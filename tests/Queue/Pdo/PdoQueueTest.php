<?php

namespace Phive\Queue\Tests\Queue\Pdo;

use Phive\Queue\NoItemAvailableException;
use Phive\Queue\Tests\Handler\PdoHandler;
use Phive\Queue\Tests\Queue\ConcurrencyTrait;
use Phive\Queue\Tests\Queue\PerformanceTrait;
use Phive\Queue\Tests\Queue\QueueTest;
use Phive\Queue\Tests\Queue\UtilTrait;

abstract class PdoQueueTest extends QueueTest
{
    use ConcurrencyTrait;
    use PerformanceTrait;
    use UtilTrait;

    public function getUnsupportedItemTypes()
    {
        return ['array', 'object'];
    }

    /**
     * @dataProvider provideItemsOfUnsupportedTypes
     * @expectedException \PHPUnit_Framework_Error
     * @expectedExceptionMessage PDO::quote() expects parameter 1 to be string
     */
    public function testGetErrorOnUnsupportedItemType($item)
    {
        $this->queue->push($item);
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
     */
    public function testThrowExceptionOnUnsupportedDriver()
    {
        $pdo = new MockPdo();
        $pdo->driverName = 'unsupported_driver';

        $handler = self::getHandler();
        $class = $handler->getQueueClass();

        new $class($pdo, $handler->getOption('table_name'));
    }

    /**
     * @dataProvider provideUnsupportedErrorModes
     * @expectedException \InvalidArgumentException
     */
    public function testThrowExceptionOnUnsupportedErrorMode($errorMode)
    {
        $pdo = new MockPdo();
        $pdo->errorMode = $errorMode;

        $handler = self::getHandler();
        $class = $handler->getQueueClass();

        new $class($pdo, $handler->getOption('table_name'));
    }

    public function provideUnsupportedErrorModes()
    {
        return [
            [\PDO::ERRMODE_SILENT],
            [\PDO::ERRMODE_WARNING],
        ];
    }
}
