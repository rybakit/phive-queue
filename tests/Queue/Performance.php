<?php

/*
 * This file is part of the Phive Queue package.
 *
 * (c) Eugene Leonovich <gen.work@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phive\Queue\Tests\Queue;

trait Performance
{
    /**
     * @group performance
     * @dataProvider providePerformancePopDelay
     */
    public function testPushPopPerformance($delay)
    {
        $queueSize = static::getPerformanceQueueSize();
        $queueName = preg_replace('~^'.preg_quote(__NAMESPACE__).'\\\|Test$~', '', get_class($this));
        $item = str_repeat('x', static::getPerformanceItemLength());

        printf("\n%s::push()%s\n", $queueName, $delay ? ' (delayed)' : '');

        $runtime = $this->benchmarkPush($queueSize, $item, $delay);
        $this->printPerformanceResult($queueSize, $runtime);

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

    public function providePerformancePopDelay()
    {
        return [[0], [1]];
    }

    protected function benchmarkPush($queueSize, $item, $delay)
    {
        $eta = $delay ? time() + $delay : null;

        $start = microtime(true);
        for ($i = $queueSize; $i; $i--) {
            $this->queue->push($item, $eta);
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

    protected static function getPerformanceQueueSize()
    {
        return (int) getenv('PHIVE_PERF_QUEUE_SIZE');
    }

    protected static function getPerformanceItemLength()
    {
        return (int) getenv('PHIVE_PERF_ITEM_LENGTH');
    }
}
