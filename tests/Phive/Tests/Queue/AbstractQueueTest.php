<?php

namespace Phive\Tests\Queue;

abstract class AbstractQueueTest extends \PHPUnit_Framework_TestCase
{
    public function testPushPop()
    {
        $item = $this->createUniqueItem();
        $queue = $this->createQueue();

        $this->assertFalse($queue->pop());
        $queue->push($item);
        $this->assertEquals($item, $queue->pop());
        $this->assertFalse($queue->pop());
    }

    public function testPushPopOrder()
    {
        $i1 = $this->createUniqueItem();
        $i2 = $this->createUniqueItem();

        $queue = $this->createQueue();
        $queue->push($i1, '+2 seconds');
        $queue->push($i2);

        $this->assertEquals($i2, $queue->pop());
        $this->assertFalse($queue->pop());
        sleep(2);
        $this->assertEquals($i1, $queue->pop());
    }

    public function testPeek()
    {
        $i1 = $this->createUniqueItem();
        $i2 = $this->createUniqueItem();
        $i3 = $this->createUniqueItem();

        $queue = $this->createQueue();
        $queue->push($i1);
        $queue->push($i2);
        $queue->push($i3);

        $items = $queue->peek(2, 1);
        $this->assertInstanceOf('Iterator', $items);

        $items->rewind();
        $this->assertEquals($i2, $items->current());
        $items->next();
        $this->assertEquals($i3, $items->current());
        $items->next();
        $this->assertEmpty($items->current());
    }

    public function testPeekLimitRange()
    {
        $queue = $this->createQueue();

        try {
            $items = $queue->peek(0);
            $this->fail('peek() throws an \OutOfRangeException if limit <= 0 and != -1');
        } catch (\Exception $e) {
            $this->assertInstanceOf('OutOfRangeException', $e, 'peek() throws an \OutOfRangeException if limit <=0 and != -1');
            $this->assertEquals('Parameter limit must either be -1 or a value greater than 0.', $e->getMessage());
        }
    }

    public function testPeekSkipRange()
    {
        $queue = $this->createQueue();

        try {
            $items = $queue->peek(1, -1);
            $this->fail('peek() throws an \OutOfRangeException if skip less then 0');
        } catch (\Exception $e) {
            $this->assertInstanceOf('OutOfRangeException', $e, 'peek() throws an \OutOfRangeException if skip less then 0');
            $this->assertEquals('Parameter skip must be greater than or equal 0.', $e->getMessage());
        }
    }

    public function testPeekWithoutLimitAndSkip()
    {
        $count = 5;
        $queue = $this->createQueue();

        for ($i = 0; $i < $count; $i++) {
            $queue->push($i);
        }

        $i = 0;
        foreach ($queue->peek(-1, 0) as $item) {
            $i++;
        }

        $this->assertEquals($count, $i);
    }

    public function testCountAndClear()
    {
        $queue = $this->createQueue();
        $this->assertEquals(0, $queue->count());

        $item = $this->createUniqueItem();
        for ($i = 0; $i < 7; $i++) {
            $queue->push($item);
        }
        $this->assertEquals(7, $queue->count());

        $queue->clear();
        $this->assertEquals(0, $queue->count());
    }

    protected function createUniqueItem()
    {
        return uniqid('item_');
    }

    /**
     * @return \Phive\Queue\AdvancedQueueInterface
     */
    abstract protected function createQueue();
}
