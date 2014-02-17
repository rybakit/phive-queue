<?php

namespace Phive\Queue\Tests\Queue;

use Phive\Queue\Exception\NoItemAvailableException;
use Phive\Queue\Queue\QueueInterface;

abstract class AbstractQueueTest extends \PHPUnit_Framework_TestCase
{
    const PERF_ITEM_LENGTH = 16;

    /**
     * @var int Timestamp that will be returned by time().
     */
    public static $now;

    /**
     * @var \Phive\Queue\Queue\QueueInterface
     */
    protected $queue;

    /**
     * Whether the queue supports an expired ETA or not.
     *
     * @var bool
     */
    protected $supportsExpiredEta = true;

    public function testQueueImplementsQueueInterface()
    {
        $this->assertInstanceOf('Phive\Queue\Queue\QueueInterface', $this->queue);
    }

    public function testPushPop()
    {
        $this->queue->push('item');

        $this->assertEquals('item', $this->queue->pop());
        $this->assertNoItemAvailableException($this->queue);
    }

    public function testPopOrder()
    {
        if ($this->supportsExpiredEta) {
            $this->queue->push('item1');
            $this->queue->push('item2', '-1 hour');
        } else {
            $this->queue->push('item1', '+3 seconds');
            $this->queue->push('item2');
        }

        $this->assertEquals('item2', $this->queue->pop());
        if (!$this->supportsExpiredEta) {
            sleep(3);
        }
        $this->assertEquals('item1', $this->queue->pop());
    }

    public function testPopDelay()
    {
        $eta = time() + 3;

        $this->queue->push('item', $eta);
        $this->assertNoItemAvailableException($this->queue);

        $this->callInFuture(function () {
            $this->assertEquals('item', $this->queue->pop());
        }, $eta, !$this->supportsExpiredEta);
    }

    public function testCountAndClear()
    {
        $this->assertEquals(0, $this->queue->count());

        for ($i = $count = 5; $i; $i--) {
            $this->queue->push('item'.$i);
        }

        $this->assertEquals($count, $this->queue->count());

        $this->queue->clear();
        $this->assertEquals(0, $this->queue->count());
    }

    /**
     * @group performance
     */
    public function testPushPopPerformance()
    {
        $queueSize = (int) $GLOBALS['performance_queue_size'];
        $item = str_repeat('x', self::PERF_ITEM_LENGTH);

        echo sprintf("\n%s::push()\n", get_class($this->queue));

        $start = microtime(true);
        for ($i = $queueSize; $i; $i--) {
            $this->queue->push($item);
        }
        $this->printPerformanceResult($queueSize, microtime(true) - $start);

        echo sprintf("\n%s::pop()\n", get_class($this->queue));

        $start = microtime(true);
        for ($i = $queueSize; $i; $i--) {
            $this->queue->pop();
        }

        $this->printPerformanceResult($queueSize, microtime(true) - $start);
    }

    protected function setUp()
    {
        $this->queue = $this->createQueue();

        self::$now = null;
        $this->stubTimeFunction();
    }

    protected function assertNoItemAvailableException(QueueInterface $queue)
    {
        try {
            $queue->pop();
        } catch (NoItemAvailableException $e) {
            return;
        }

        $this->fail('An expected NoItemAvailableException has not been raised.');
    }

    protected function callInFuture(\Closure $func, $futureTime, $sleep = false)
    {
        if ($sleep) {
            sleep($futureTime - time());
            return $func($this);
        }

        self::$now = $futureTime;
        $result = $func();
        self::$now = null;

        return $result;
    }

    protected function printPerformanceResult($total, $runtime)
    {
        echo sprintf("   Total operations:      %d\n", $total);
        echo sprintf("   Operations per second: %01.3f [#/sec]\n", $total / $runtime);
        echo sprintf("   Time per operation:    %01.3f [ms]\n", ($runtime / $total) * 1000000);
        echo sprintf("   Time taken for test:   %01.3f [sec]\n", $runtime);
    }

    private function stubTimeFunction()
    {
        $class = get_class($this->queue);
        $namespace = substr($class, 0, strrpos($class, '\\'));

        // this code should be evaluated directly after the queue class is loaded
        // and before any queue method is called
        if (!is_callable("$namespace\\time")) {
            eval('namespace '.$namespace.' { function time() { return \\'.__CLASS__.'::$now ?: \time(); }}');
        }
    }

    /**
     * @return \Phive\Queue\Queue\QueueInterface
     */
    abstract public function createQueue();
}
