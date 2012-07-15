<?php

namespace Phive\Tests\Queue;

abstract class HandlerAwareQueueTest extends AbstractQueueTest
{
    /**
     * @var AbstractHandler
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

    public function setUp()
    {
        parent::setUp();

        static::$handler->clear();
    }

    /**
     * @return \Phive\Queue\QueueInterface
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

        $workerIds = array();
        $poppedItems = array();
        $client->setCompleteCallback(function($task) use (&$workerIds, &$poppedItems) {
            $data = json_decode($task->data(), true);
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

        // run the tasks in parallel (assuming multiple workers)
        if (!$client->runTasks()) {
            throw new \RuntimeException($client->error());
        }

        $this->assertEquals($queueSize, count($poppedItems));
        $this->assertGreaterThan(1, count($workerIds), 'Not enough workers to test concurrency.');
    }

    /**
     * Abstract static class functions are not possible since v5.2.
     *
     * @throws \BadMethodCallException
     */
    public static function createHandler()
    {
        throw new \BadMethodCallException(
            sprintf('Method %s:%s is not implemented.', get_called_class(), __FUNCTION__)
        );
    }
}
