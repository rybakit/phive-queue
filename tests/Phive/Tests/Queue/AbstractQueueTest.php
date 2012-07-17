<?php

namespace Phive\Tests\Queue;

abstract class AbstractQueueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Phive\Queue\QueueInterface
     */
    protected $queue;

    public function setUp()
    {
        $this->queue = $this->createQueue();
    }

    public function testQueueImplementsQueueInterface()
    {
        $this->assertInstanceOf('Phive\Queue\QueueInterface', $this->queue);
    }

    public function testPushPop()
    {
        $item = $this->createUniqueItem();

        $this->assertFalse($this->queue->pop());
        $this->queue->push($item);
        $this->assertEquals($item, $this->queue->pop());
        $this->assertFalse($this->queue->pop());
    }

    public function testPushPopOrder()
    {
        $i1 = $this->createUniqueItem();
        $i2 = $this->createUniqueItem();

        $this->queue->push($i1, '+2 seconds');
        $this->queue->push($i2);

        $this->assertEquals($i2, $this->queue->pop());
        $this->assertFalse($this->queue->pop());
        sleep(2);
        $this->assertEquals($i1, $this->queue->pop());
    }

    public function testPeek()
    {
        $i1 = $this->createUniqueItem();
        $i2 = $this->createUniqueItem();
        $i3 = $this->createUniqueItem();

        $this->queue->push($i1);
        $this->queue->push($i2);
        $this->queue->push($i3);

        $items = $this->queue->peek(2, 1);
        $this->assertInstanceOf('Iterator', $items);

        $items->rewind();
        $this->assertEquals($i2, $items->current());
        $items->next();
        $this->assertEquals($i3, $items->current());
        $items->next();
        $this->assertEmpty($items->current());
    }

    public function testPeekAll()
    {
        $count = 5;

        for ($i = $count; $i; $i--) {
            $this->queue->push($i);
        }

        $items = $this->queue->peek(-1, 0);

        $this->assertInstanceOf('Iterator', $items);
        $this->assertEquals($count, iterator_count($items));
    }

    public function testPeekThrowsExceptionOnInvalidLimitRange()
    {
        try {
            $items = $this->queue->peek(0);
        } catch (\Exception $e) {
            $this->assertInstanceOf('OutOfRangeException', $e, 'peek() throws an \OutOfRangeException if limit <=0 and != -1');
            $this->assertEquals('Parameter limit must either be -1 or a value greater than 0.', $e->getMessage());
            return;
        }

        $this->fail('peek() throws an \OutOfRangeException if limit <= 0 and != -1');
    }

    public function testPeekThrowsExceptionOnInvalidSkipRange()
    {
        try {
            $items = $this->queue->peek(1, -1);
        } catch (\Exception $e) {
            $this->assertInstanceOf('OutOfRangeException', $e, 'peek() throws an \OutOfRangeException if skip less then 0');
            $this->assertEquals('Parameter skip must be greater than or equal 0.', $e->getMessage());
            return;
        }

        $this->fail('peek() throws an \OutOfRangeException if skip less then 0');
    }

    public function testCountAndClear()
    {
        $count = 5;

        $this->assertEquals(0, $this->queue->count());
        for ($i = $count; $i; $i--) {
            $this->queue->push($i);
        }
        $this->assertEquals($count, $this->queue->count());

        $this->queue->clear();
        $this->assertEquals(0, $this->queue->count());
    }

    /**
     * @group extra
     * @group benchmark
     */
    public function testPushPopSpeed()
    {
        $queueSize = (int) $GLOBALS['benchmark_queue_size'];

        echo sprintf("Benchmarking \"%s\" with %d item(s):\n", get_class($this->queue), $queueSize);

        $start = microtime(true);
        for ($i = $queueSize; $i; $i--) {
            $this->queue->push($i);
        }

        echo sprintf(" > pushing: %s seconds\n", microtime(true) - $start);

        $start = microtime(true);
        for ($i = $queueSize; $i; $i--) {
            $this->queue->push($i);
        }

        echo sprintf(" > popping: %s seconds\n", microtime(true) - $start);
    }

    protected function createUniqueItem()
    {
        return uniqid('item_', true);
    }

    /**
     * @return \Phive\Queue\QueueInterface
     */
    abstract public function createQueue();
}
