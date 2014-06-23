<?php

namespace Phive\Queue\Tests\Queue;

use Phive\Queue\NoItemAvailableException;
use Phive\Queue\Queue;
use Phive\Queue\Tests as t;

abstract class QueueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Queue
     */
    protected $queue;

    /**
     * Whether the queue supports an expired ETA or not.
     *
     * @var bool
     */
    protected $supportsExpiredEta = true;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->queue = $this->createQueue();
    }

    public function testQueueImplementsQueueInterface()
    {
        $this->assertInstanceOf('Phive\Queue\Queue', $this->queue);
    }

    public function testPushPop()
    {
        $this->queue->push('item');

        $this->assertEquals('item', $this->queue->pop());
        $this->assertNoItemIsAvailable($this->queue);
    }

    public function testPopOrder()
    {
        if ($this->supportsExpiredEta) {
            $this->queue->push('item1');
            $this->queue->push('item2', '-1 hour');
        } else {
            $this->queue->push('item1', '+3 seconds');
            $this->queue->push('item2');
        }

        $this->assertEquals('item2', $this->queue->pop());
        if (!$this->supportsExpiredEta) {
            sleep(3);
        }
        $this->assertEquals('item1', $this->queue->pop());
    }

    /**
     * @requires extension uopz
     */
    public function testPopDelay()
    {
        $eta = time() + 3;

        $this->queue->push('item', $eta);
        $this->assertNoItemIsAvailable($this->queue);

        if (!$this->supportsExpiredEta) {
            sleep(3);
            $this->assertEquals('item', $this->queue->pop());
        } else {
            t\freeze_time($eta);
            $this->assertEquals('item', $this->queue->pop());
            t\unfreeze_time();
        }
    }

    public function testPushWithExpiredEta()
    {
        $this->queue->push('item', time() - 1);
        $this->assertEquals('item', $this->queue->pop());
    }

    public function testCountAndClear()
    {
        $this->assertEquals(0, $this->queue->count());

        for ($i = $count = 5; $i; $i--) {
            $this->queue->push('item'.$i);
        }

        $this->assertEquals($count, $this->queue->count());

        $this->queue->clear();
        $this->assertEquals(0, $this->queue->count());
    }

    /**
     * @dataProvider provideItemsOfSupportedTypes
     */
    public function testSupportedItemTypeLoose($item)
    {
        $this->queue->push($item);
        $this->assertEquals($item, $this->queue->pop());
    }

    public function provideItemsOfVariousTypes()
    {
        return [
            'null'      => [null],
            'bool'      => [true],
            'int'       => [42],
            'float'     => [1.5],
            'string'    => ['string'],
            'array'     => [['a','r','r','a','y']],
            'object'    => [new \stdClass()],
        ];
    }

    public function provideItemsOfSupportedTypes()
    {
        return array_diff_key(
            $this->provideItemsOfVariousTypes(),
            array_fill_keys($this->getUnsupportedItemTypes(), false)
        );
    }

    public function getUnsupportedItemTypes()
    {
        return [];
    }

    protected function assertNoItemIsAvailable(Queue $queue)
    {
        try {
            $queue->pop();
        } catch (NoItemAvailableException $e) {
            return;
        }

        $this->fail('An expected NoItemAvailableException has not been raised.');
    }

    /**
     * @return Queue
     */
    abstract public function createQueue();
}
