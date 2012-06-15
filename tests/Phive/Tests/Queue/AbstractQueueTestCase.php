<?php

namespace Phive\Tests\Queue;

abstract class AbstractQueueTestCase extends \PHPUnit_Framework_TestCase
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

        for ($i = 0; $i < $count; $i++) {
            $this->queue->push($i);
        }

        $i = 0;
        foreach ($this->queue->peek(-1, 0) as $item) {
            $i++;
        }

        $this->assertEquals($count, $i);
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

        $item = $this->createUniqueItem();
        for ($i = 0; $i < $count; $i++) {
            $this->queue->push($item);
        }
        $this->assertEquals($count, $this->queue->count());

        $this->queue->clear();
        $this->assertEquals(0, $this->queue->count());
    }

    public function testBinaryDataSupport()
    {
        $item = "\x04\x00\xa0\x00\x501";
        //$item = file_get_contents('/bin/tailf');
        //$item = base64_decode('wAD4Af8B/gHuA/4BzgP1A/8P/h//f/xv+z30D9IDSAE=');
        //$item = 0x7f454c46020101;

        //$item = array(1, new \stdClass());

        //$item = new \Phive\Queue\InMemoryQueue();
        //$queue = new \Phive\Queue\SerializerAwareQueue($this->queue, new \Phive\Serializer\IgbinarySerializer());

        $this->queue->push($item);

        //$this->assertEquals(0, strcmp($item, $this->queue->pop()));
        $this->assertEquals($item, $this->queue->pop());
    }

    protected function createUniqueItem()
    {
        return uniqid('item_');
    }

    /**
     * @return \Phive\Queue\QueueInterface
     */
    abstract protected function createQueue();
}
