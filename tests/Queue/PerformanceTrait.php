<?php

namespace Phive\Queue\Tests\Queue;

trait PerformanceTrait
{
    protected static $performanceItemLength = 16;

    /**
     * @group performance
     * @dataProvider providePerformanceData
     */
    public function testPushPopPerformance($benchmarkMethod, $delay)
    {
        $queueSize = $this->getPerformanceQueueSize();
        $queueName = preg_replace('~^'.preg_quote(__NAMESPACE__).'\\\|Test$~', '', get_class($this));
        $item = str_repeat('x', static::$performanceItemLength);

        printf("\n%s::push()%s\n", $queueName, $delay ? ' (delayed)' : '');

        $this->printPerformanceResult($queueSize, $this->$benchmarkMethod($queueSize, $item));

        if ($delay) {
            sleep($delay);
        }

        printf("\n%s::pop()%s\n", $queueName, $delay ? ' (delayed)' : '');

        $start = microtime(true);
        for ($i = $queueSize; $i; $i--) {
            $this->queue->pop();
        }

        $this->printPerformanceResult($queueSize, microtime(true) - $start);
    }

    public function providePerformanceData()
    {
        return [
            ['benchmarkPush', 0],
            ['benchmarkPushDelayed', 1],
        ];
    }

    public function benchmarkPush($queueSize, $item)
    {
        $start = microtime(true);
        for ($i = $queueSize; $i; $i--) {
            $this->queue->push($item);
        }

        return microtime(true) - $start;
    }

    public function benchmarkPushDelayed($queueSize, $item)
    {
        $start = microtime(true);
        for ($i = $queueSize; $i; $i--) {
            $this->queue->push($item, time() + 1);
        }

        return microtime(true) - $start;
    }

    protected function printPerformanceResult($total, $runtime)
    {
        printf("   Total operations:      %d\n", $total);
        printf("   Operations per second: %01.3f [#/sec]\n", $total / $runtime);
        printf("   Time per operation:    %01.3f [ms]\n", ($runtime / $total) * 1000000);
        printf("   Time taken for test:   %01.3f [sec]\n", $runtime);
    }

    protected function getPerformanceQueueSize()
    {
        return (int) getenv('PHIVE_PERF_QUEUE_SIZE');
    }
}
