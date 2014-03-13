<?php

namespace Phive\Queue\Tests\Queue;

trait PerformanceTrait
{
    protected static $performanceItemLength = 16;

    /**
     * @group performance
     */
    public function testPushPopPerformance()
    {
        $queueSize = $this->getPerformanceQueueSize();
        $queueName = preg_replace('~^'.preg_quote(__NAMESPACE__).'\\\|Test$~', '', get_class($this));
        $item = str_repeat('x', static::$performanceItemLength);

        echo sprintf("\n%s::push()\n", $queueName);

        $start = microtime(true);
        for ($i = $queueSize; $i; $i--) {
            $this->queue->push($item);
        }
        $this->printPerformanceResult($queueSize, microtime(true) - $start);

        echo sprintf("\n%s::pop()\n", $queueName);

        $start = microtime(true);
        for ($i = $queueSize; $i; $i--) {
            $this->queue->pop();
        }

        $this->printPerformanceResult($queueSize, microtime(true) - $start);
    }

    protected function printPerformanceResult($total, $runtime)
    {
        echo sprintf("   Total operations:      %d\n", $total);
        echo sprintf("   Operations per second: %01.3f [#/sec]\n", $total / $runtime);
        echo sprintf("   Time per operation:    %01.3f [ms]\n", ($runtime / $total) * 1000000);
        echo sprintf("   Time taken for test:   %01.3f [sec]\n", $runtime);
    }

    protected function getPerformanceQueueSize()
    {
        return (int) getenv('PHIVE_PERF_QUEUE_SIZE');
    }
}
