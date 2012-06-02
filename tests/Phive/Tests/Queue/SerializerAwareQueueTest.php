<?php

namespace Phive\Tests\Queue;

use Phive\Queue\QueueInterface;
use Phive\Queue\SerializerAwareQueue;

class SerializerAwareQueueTest extends \PHPUnit_Framework_TestCase
{
    public function testQueueGetter()
    {
        $queue = $this->getMock('Phive\Queue\QueueInterface');
        $outerQueue = new SerializerAwareQueue($queue);

        $this->assertEquals($queue, $outerQueue->getQueue());
    }

    public function testSerializerGetter()
    {
        $queue = $this->getMock('Phive\Queue\QueueInterface');
        $serializer = $this->getMock('Phive\Serializer\SerializerInterface');
        $outerQueue = new SerializerAwareQueue($queue, $serializer);

        $this->assertEquals($serializer, $outerQueue->getSerializer());
    }

    public function testPush()
    {
        $item = array(1, 'text', new \stdClass());
        $serializedItem = serialize($item);

        $queue = $this->getMock('Phive\Queue\QueueInterface');
        $queue->expects($this->once())
            ->method('push')
            ->with($this->isType('string'));

        $serializer = $this->getMock('Phive\Serializer\SerializerInterface');
        $serializer->expects($this->once())
            ->method('serialize')
            ->with($item)
            ->will($this->returnValue($serializedItem));

        $outerQueue = new SerializerAwareQueue($queue, $serializer);
        $outerQueue->push($item);
    }

    public function testPop()
    {
        $item = array(1, 'text', new \stdClass());
        $serializedItem = serialize($item);

        $queue = $this->getMock('Phive\Queue\QueueInterface');
        $queue->expects($this->once())
            ->method('pop')
            ->will($this->returnValue($serializedItem));

        $serializer = $this->getMock('Phive\Serializer\SerializerInterface');
        $serializer->expects($this->once())
            ->method('unserialize')
            ->with($serializedItem)
            ->will($this->returnValue(unserialize($serializedItem)));

        $outerQueue = new SerializerAwareQueue($queue, $serializer);
        $this->assertEquals($item, $outerQueue->pop($item));
    }

    public function testPeek()
    {
        $i1 = array(1);
        $i2 = 'text';
        $i3 = new \stdClass();

        $si1 = serialize($i1);
        $si2 = serialize($i2);
        $si3 = serialize($i3);

        $queue = $this->getMock('Phive\Queue\QueueInterface');
        $queue->expects($this->once())
            ->method('peek')
            ->will($this->returnValue(new \ArrayIterator(array($si1, $si2, $si3))));

        $serializer = $this->getMock('Phive\Serializer\SerializerInterface');
        $serializer->expects($this->exactly(3))
            ->method('unserialize')
            ->will($this->returnCallback(function($serializedItem) {
                return unserialize($serializedItem);
        }));

        $outerQueue = new SerializerAwareQueue($queue, $serializer);
        $items = $outerQueue->peek();

        $items->rewind();
        $this->assertEquals($i1, $items->current());
        $items->next();
        $this->assertEquals($i2, $items->current());
        $items->next();
        $this->assertEquals($i3, $items->current());
    }

    public function testCount()
    {
        $count = 345;

        $queue = $this->getMock('Phive\Queue\QueueInterface');
        $queue->expects($this->once())
            ->method('count')
            ->will($this->returnValue($count));

        $outerQueue = new SerializerAwareQueue($queue);
        $this->assertEquals($count, $outerQueue->count());
    }

    public function testClear()
    {
        $queue = $this->getMock('Phive\Queue\QueueInterface');
        $queue->expects($this->once())
            ->method('clear');

        $outerQueue = new SerializerAwareQueue($queue);
        $outerQueue->clear();
    }
}
