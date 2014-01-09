<?php

namespace Phive\Queue\Tests\Queue;

abstract class AbstractPersistentQueueTest extends AbstractQueueTest
{
    /**
     * @var \Phive\Queue\Tests\Handler\AbstractHandler
     */
    private static $handler;

    /**
     * @return \Phive\Queue\Queue\QueueInterface
     */
    public function createQueue()
    {
        return self::getHandler()->createQueue();
    }

    /**
     * @group concurrency
     */
    public function testConcurrency()
    {
        if (!class_exists('GearmanClient', false)) {
            $this->markTestSkipped('pecl/gearman is required for this test to run.');
        }

        $client = new \GearmanClient();
        $client->addServer();
        //$client->setTimeout(30);

        $workerIds = array();
        $poppedItems = array();
        $client->setCompleteCallback(function(\GearmanTask $task) use (&$workerIds, &$poppedItems) {
            $data = explode(':', $task->data(), 2);
            if (!is_array($data) || 2 != count($data)) {
                return;
            }

            list($workerId, $item) = $data;

            $workerIds[$workerId] = true;

            if (!isset($poppedItems[$item])) {
                $poppedItems[$item] = true;
            }
        });

        $queueSize = (int) $GLOBALS['concurrency_queue_size'];
        $this->assertGreaterThan(1, $queueSize, 'Queue size is too small to test concurrency.');

        $workload = serialize(self::getHandler());
        for ($i = 1; $i <= $queueSize; $i++) {
            $this->queue->push($i);
            $client->addTask('pop', $workload);
        }

        try {
            // run the tasks in parallel (assuming multiple workers)
            $result = $client->runTasks();
        } catch (\GearmanException $e) {
            $result = false;
        }

        if (!$result) {
            $this->markTestSkipped('Unable to run gearman tasks. Check if gearman server is running.');
        }

        $this->assertEquals($queueSize, count($poppedItems));
        $this->assertGreaterThan(1, count($workerIds), 'Not enough workers to test concurrency.');
    }

    /**
     * Abstract static class functions are not supported since v5.2.
     *
     * @param array $config
     *
     * @return \Phive\Queue\Tests\Handler\AbstractHandler
     *
     * @throws \BadMethodCallException
     */
    public static function createHandler(array $config)
    {
        throw new \BadMethodCallException(
            sprintf('Method %s:%s is not implemented.', get_called_class(), __FUNCTION__)
        );
    }

    public static function getHandler()
    {
        if (!self::$handler) {
            self::$handler = static::createHandler($GLOBALS);
        }

        return self::$handler;
    }

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::getHandler()->reset();
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        self::$handler = null;
    }

    protected function setUp()
    {
        parent::setUp();

        self::getHandler()->clear();
    }
}
