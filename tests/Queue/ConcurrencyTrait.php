<?php

namespace Phive\Queue\Tests\Queue;

trait ConcurrencyTrait
{
    use PersistenceTrait;

    /**
     * @group concurrency
     */
    public function testConcurrency()
    {
        if (!class_exists('GearmanClient', false)) {
            $this->markTestSkipped('pecl/gearman is required for this test to run.');
        }

        $client = new \GearmanClient();

        if (!$client->addServer('127.0.0.1')) {
            $this->markTestSkipped('Failed to add gearman job server (127.0.0.1).');
        }

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

        $queueSize = $this->getConcurrencyQueueSize();
        $this->assertGreaterThan(10, $queueSize, 'Queue size is too small to test concurrency.');

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

    protected function getConcurrencyQueueSize()
    {
        return (int) getenv('PHIVE_CONCUR_QUEUE_SIZE');
    }
}
