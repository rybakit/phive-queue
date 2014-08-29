<?php

namespace Phive\Queue\Tests\Queue;

use Phive\Queue\TypeSafeQueue;

class TypeSafeQueueTest extends \PHPUnit_Framework_TestCase
{
    use UtilTrait;

    /**
     * @dataProvider provideItemsOfSupportedTypes
     */
    public function testPush($item)
    {
        $serializedItem = null;

        $mock = $this->getQueueMock();
        $mock->expects($this->once())->method('push')
            ->with($this->callback(function ($subject) use (&$serializedItem) {
                $serializedItem = $subject;

                return is_string($subject) && !preg_match('/[\\x00-\\x08\\x0b\\x0c\\x0e-\\x1f\\x7f]/', $subject);
            }));

        $queue = new TypeSafeQueue($mock);
        $queue->push($item);

        return ['original' => $item, 'serialized' => $serializedItem];
    }

    /**
     * @depends testPush
     */
    public function testPop($data)
    {
        $mock = $this->getQueueMock();
        $mock->expects($this->once())->method('pop')
            ->will($this->returnValue($data['serialized']));

        $queue = new TypeSafeQueue($mock);

        $this->assertEquals($data['original'], $queue->pop());
    }

    public function testCount()
    {
        $mock = $this->getQueueMock();
        $mock->expects($this->once())->method('count')->will($this->returnValue(42));

        $queue = new TypeSafeQueue($mock);

        $this->assertSame(42, $queue->count());
    }

    public function testClear()
    {
        $mock = $this->getQueueMock();
        $mock->expects($this->once())->method('clear');

        $queue = new TypeSafeQueue($mock);
        $queue->clear();
    }
}
