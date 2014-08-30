<?php

namespace Phive\Queue\Tests\Queue;

use Phive\Queue\TypeSafeQueue;

class TypeSafeQueueTest extends \PHPUnit_Framework_TestCase
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
        $this->queue = new TypeSafeQueue($this->mock);
    }

    /**
     * @dataProvider provideItemsOfSupportedTypes
     */
    public function testPush($item)
    {
        $serializedItem = null;

        $this->mock->expects($this->once())->method('push')
            ->with($this->callback(function ($subject) use (&$serializedItem) {
                $serializedItem = $subject;

                return is_string($subject) && ctype_print($subject);
            }));

        $this->queue->push($item);

        return ['original' => $item, 'serialized' => $serializedItem];
    }

    /**
     * @depends testPush
     */
    public function testPop($data)
    {
        $this->mock->expects($this->once())->method('pop')
            ->will($this->returnValue($data['serialized']));

        $this->assertEquals($data['original'], $this->queue->pop());
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
}
