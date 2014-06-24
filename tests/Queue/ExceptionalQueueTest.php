<?php

namespace Phive\Queue\Tests\Queue;

use Phive\Queue\ExceptionalQueue;
use Phive\Queue\QueueException;

class ExceptionalQueueTest extends \PHPUnit_Framework_TestCase
{
    use UtilTrait;

    public function testGetInnerQueue()
    {
        $mock = $this->getQueueMock();
        $queue = new ExceptionalQueue($mock);

        $this->assertEquals($mock, $queue->getInnerQueue());
    }

    /**
     * @dataProvider provideQueueInterfaceMethods
     */
    public function testReturnOriginalResult($method)
    {
        if (in_array($method, ['push', 'clear'], true)) {
            return;
        }

        $mock = $this->getQueueMock();
        $mock->expects($this->any())->method($method)->will($this->returnValue($method));

        $queue = new ExceptionalQueue($mock);

        $this->assertEquals($method, $this->callQueueMethod($queue, $method));
    }

    /**
     * @dataProvider provideQueueInterfaceMethods
     * @expectedException \Phive\Queue\QueueException
     */
    public function testThrowOriginalQueueException($method)
    {
        $mock = $this->getQueueMock();
        $exception = new QueueException($mock);
        $mock->expects($this->any())->method($method)->will($this->throwException($exception));

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
        $mock->expects($this->any())->method($method)->will($this->throwException($exception));

        $queue = new ExceptionalQueue($mock);

        $this->callQueueMethod($queue, $method);
    }
}
