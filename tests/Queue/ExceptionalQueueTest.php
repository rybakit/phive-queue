<?php

namespace Phive\Queue\Tests\Queue;

use Phive\Queue\ExceptionalQueue;
use Phive\Queue\QueueException;

class ExceptionalQueueTest extends \PHPUnit_Framework_TestCase
{
    use UtilTrait;

    protected $mock;
    protected $queue;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->mock = $this->getQueueMock();
        $this->queue = new ExceptionalQueue($this->mock);
    }

    public function testPush()
    {
        $item = 'foo';

        $this->mock->expects($this->once())->method('push')
            ->with($this->equalTo($item));

        $this->queue->push($item);
    }

    public function testPop()
    {
        $this->mock->expects($this->once())->method('pop');
        $this->queue->pop();
    }

    public function testCount()
    {
        $this->mock->expects($this->once())->method('count')
            ->will($this->returnValue(42));

        $this->assertSame(42, $this->queue->count());
    }

    public function testClear()
    {
        $this->mock->expects($this->once())->method('clear');
        $this->queue->clear();
    }

    /**
     * @dataProvider provideQueueInterfaceMethods
     * @expectedException \Phive\Queue\QueueException
     */
    public function testThrowOriginalQueueException($method)
    {
        $this->mock->expects($this->once())->method($method)
            ->will($this->throwException(new QueueException($this->mock)));

        $this->callQueueMethod($this->queue, $method);
    }

    /**
     * @dataProvider provideQueueInterfaceMethods
     * @expectedException \Phive\Queue\QueueException
     */
    public function testThrowWrappedQueueException($method)
    {
        $this->mock->expects($this->once())->method($method)
            ->will($this->throwException(new \Exception()));

        $this->callQueueMethod($this->queue, $method);
    }
}
