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
     * @group concurency
     */
    public function testConcurency()
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

        $numOfJobs = 100;
        $workload = serialize(static::$handler);

        for ($i = $numOfJobs; $i; $i--) {
            $this->queue->push($i);
            $client->addTask('pop', $workload);
        }

        // run the tasks in parallel (assuming multiple workers)
        if (!$client->runTasks()) {
            throw new \RuntimeException($client->error());
        }

        $this->assertEquals($numOfJobs, count($poppedItems));
        $this->assertGreaterThan(1, count($workerIds), 'Not enough workers to test concurency.');

        //static::$handler->reset();
    }

    /*
    public function taskCompleted($task)
    {
        $this->concurencyQueueSize--;
    }
    */

    abstract public static function createHandler();
}
