<?php

namespace Phive\Queue\Tests\Queue;

use Phive\Queue\ExceptionalQueue;
use Phive\Queue\QueueException;

class ExceptionalQueueTest extends \PHPUnit_Framework_TestCase
{
    use UtilTrait;

    public function testPush()
    {
        $item = 'foo';

        $mock = $this->getQueueMock();
        $mock->expects($this->once())->method('push')->with($this->equalTo($item));

        $queue = new ExceptionalQueue($mock);
        $queue->push($item);
    }

    public function testPop()
    {
        $mock = $this->getQueueMock();
        $mock->expects($this->once())->method('pop');

        $queue = new ExceptionalQueue($mock);
        $queue->pop();
    }

    public function testCount()
    {
        $mock = $this->getQueueMock();
        $mock->expects($this->once())->method('count')->will($this->returnValue(42));

        $queue = new ExceptionalQueue($mock);

        $this->assertSame(42, $queue->count());
    }

    public function testClear()
    {
        $mock = $this->getQueueMock();
        $mock->expects($this->once())->method('clear');

        $queue = new ExceptionalQueue($mock);
        $queue->clear();
    }

    /**
     * @dataProvider provideQueueInterfaceMethods
     * @expectedException \Phive\Queue\QueueException
     */
    public function testThrowOriginalQueueException($method)
    {
        $mock = $this->getQueueMock();
        $exception = new QueueException($mock);
        $mock->expects($this->once())->method($method)->will($this->throwException($exception));

        $queue = new ExceptionalQueue($mock);

        $this->callQueueMethod($queue, $method);
    }

    /**
     * @dataProvider provideQueueInterfaceMethods
     * @expectedException \Phive\Queue\QueueException
     */
    public function testThrowWrappedQueueException($method)
    {
        $mock = $this->getQueueMock();
        $exception = new \Exception();
        $mock->expects($this->once())->method($method)->will($this->throwException($exception));

        $queue = new ExceptionalQueue($mock);

        $this->callQueueMethod($queue, $method);
    }
}
