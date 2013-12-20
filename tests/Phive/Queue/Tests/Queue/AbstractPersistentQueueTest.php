<?php

namespace Phive\Queue\Tests\Queue;

abstract class AbstractPersistentQueueTest extends AbstractQueueTest
{
    /**
     * @var \Phive\Queue\Tests\Handler\AbstractHandler
     */
    protected static $handler;

    public static function setUpBeforeClass()
    {
        try {
            static::$handler = static::createHandler();
        } catch (\BadMethodCallException $e) {
            throw $e;
        } catch (\Exception $e) {
            static::markTestSkipped($e->getMessage());
        }

        static::$handler->reset();
        parent::setUpBeforeClass();
    }

    /**
     * @return \Phive\Queue\Queue\QueueInterface
     *
     * @throws \LogicException
     */
    public function createQueue()
    {
        return static::$handler->createQueue();
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

        $workerIds = [];
        $poppedItems = [];
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

        $workload = serialize(static::$handler);
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
     * Abstract static class functions are not possible since v5.2.
     *
     * @return \Phive\Queue\Tests\Handler\AbstractHandler
     *
     * @throws \BadMethodCallException
     */
    public static function createHandler()
    {
        throw new \BadMethodCallException(
            sprintf('Method %s:%s is not implemented.', get_called_class(), __FUNCTION__)
        );
    }

    protected function setUp()
    {
        parent::setUp();

        static::$handler->clear();
    }
}
