<?php

namespace Phive\Tests\Queue;

abstract class AbstractQueueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var int Timestamp that will be returned by time()
     */
    public static $now;

    /**
     * @var \Phive\Queue\QueueInterface
     */
    protected $queue;

    public function testQueueImplementsQueueInterface()
    {
        $this->assertInstanceOf('Phive\Queue\QueueInterface', $this->queue);
    }

    public function testPushPop()
    {
        $this->queue->push('item');

        $this->assertEquals('item', $this->queue->pop());
        $this->assertFalse($this->queue->pop());
    }

    public function testPushPopOrder()
    {
        $this->queue->push('item1');
        $this->queue->push('item2', '-1 second');

        $this->assertEquals('item2', $this->queue->pop());
        $this->assertEquals('item1', $this->queue->pop());
    }

    public function testPushPopDelay()
    {
        $eta = time() + 5;

        $this->queue->push('item', $eta);
        $this->assertFalse($this->queue->pop());

        $queue = $this->queue;
        $this->callInFuture(function(AbstractQueueTest $self) use ($queue) {
            $self->assertEquals('item', $queue->pop());
        }, $eta);
    }

    public function testSlice()
    {
        $this->queue->push('item1');
        $this->queue->push('item2');
        $this->queue->push('itemx', '+1 hour');

        $items = $this->queue->slice(0, 100);
        $this->assertInstanceOf('Iterator', $items);

        $i = 0;
        foreach ($items as $item) {
            $this->assertEquals('item'.++$i, $item);
        }
    }

    public function testSliceOffset()
    {
        $this->queue->push('item1', '-1 second');
        $this->queue->push('item2');

        $items = $this->queue->slice(1, 100);

        $items->rewind();
        $this->assertEquals('item2', $items->current());
    }

    public function testSliceLimit()
    {
        $this->queue->push('item1', '-1 second');
        $this->queue->push('item2');

        $items = $this->queue->slice(0, 1);

        $items->rewind();
        $this->assertEquals('item1', $items->current());
        $items->next();
        $this->assertEmpty($items->current());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSliceThrowsExceptionOnInvalidOffsetType()
    {
        $this->queue->slice('invalid', 100);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSliceThrowsExceptionOnInvalidLimitType()
    {
        $this->queue->slice(0, 'invalid');
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testSliceThrowsExceptionOnInvalidOffsetRange()
    {
        $this->queue->slice(-1, 100);
    }

    /**
     * @expectedException \OutOfRangeException
     */
    public function testSliceThrowsExceptionOnInvalidLimitRange()
    {
        $this->queue->slice(0, -1);
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
     * @group benchmark
     */
    public function testPushPerformance()
    {
        echo sprintf("\n%s::push()\n", get_class($this->queue));

        $queueSize = (int) $GLOBALS['benchmark_queue_size'];
        $item = str_repeat('x', 255);

        $start = microtime(true);
        for ($i = $queueSize; $i; $i--) {
            $this->queue->push($item);
        }
        $runtime = microtime(true) - $start;

        $this->printPerformanceResult($queueSize, $runtime);
    }

    /**
     * @group   benchmark
     * @depends testPushPerformance
     */
    public function testPopPerformance()
    {
        echo sprintf("\n%s::pop()\n", get_class($this->queue));

        $queueSize = (int) $GLOBALS['benchmark_queue_size'];

        $start = microtime(true);
        for ($i = $queueSize; $i; $i--) {
            $this->queue->pop($i);
        }
        $runtime = microtime(true) - $start;

        $this->printPerformanceResult($queueSize, $runtime);
    }

    protected function setUp()
    {
        $this->queue = $this->createQueue();
        $this->stubTimeFunction();
    }

    protected function callInFuture(\Closure $func, $futureTime, $sleep = false)
    {
        if ($sleep) {
            sleep($futureTime - time());
            return $func($this);
        }

        self::$now = $futureTime;
        $result = $func($this);
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

        if (!is_callable("$namespace\\time")) {
            eval('namespace '.$namespace.' { function time() { return \\'.__CLASS__.'::$now ?: \time(); }}');
        }
    }

    /**
     * @return \Phive\Queue\QueueInterface
     */
    abstract public function createQueue();
}
