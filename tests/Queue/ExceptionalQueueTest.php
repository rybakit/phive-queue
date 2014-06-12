<?php

namespace Phive\Queue\Tests\Queue;

use Phive\Queue\ExceptionalQueue;
use Phive\Queue\QueueException;

class ExceptionalQueueTest extends \PHPUnit_Framework_TestCase
{
    public function testGetInnerQueue()
    {
        $mock = $this->getQueueMock();
        $queue = new ExceptionalQueue($mock);

        $this->assertEquals($mock, $queue->getInnerQueue());
    }

    /**
     * @dataProvider provideQueueMethods
     */
    public function testQueueReturnsOriginalResult($method)
    {
        $mock = $this->getQueueMock();
        $mock->expects($this->any())->method($method)->will($this->returnValue($method));

        $queue = new ExceptionalQueue($mock);

        $result = $this->call($queue, $method);
    }

    /**
     * @dataProvider provideQueueMethods
     * @expectedException \Phive\Queue\QueueException
     */
    public function testQueueThrowsOriginalQueueException($method)
    {
        $mock = $this->getQueueMock();
        $exception = new QueueException($mock);
        $mock->expects($this->any())->method($method)->will($this->throwException($exception));

        $queue = new ExceptionalQueue($mock);

        $this->assertEquals($method, $this->call($queue, $method));
    }

    /**
     * @dataProvider provideQueueMethods
     * @expectedException \Phive\Queue\QueueException
     */
    public function testQueueThrowsWrappedQueueException($method)
    {
        $mock = $this->getQueueMock();
        $exception = new \Exception();
        $mock->expects($this->any())->method($method)->will($this->throwException($exception));

        $queue = new ExceptionalQueue($mock);

        $this->call($queue, $method);
    }

    public function provideQueueMethods()
    {
        return array_chunk($this->getQueueMethods(), 1);
    }

    private function getQueueMethods()
    {
        return get_class_methods('Phive\Queue\Queue');
    }

    private function getQueueMock()
    {
        return $this->getMock('Phive\Queue\Queue');
    }

    private function call(ExceptionalQueue $queue, $method)
    {
        $r = new \ReflectionMethod($queue, $method);

        if ($num = $r->getNumberOfRequiredParameters()) {
            return call_user_func_array([$queue, $method], array_fill(0, $num, 'foo'));
        }

        return $queue->$method();
    }
}
