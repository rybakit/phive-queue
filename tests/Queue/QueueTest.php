<?php

/*
 * This file is part of the Phive Queue package.
 *
 * (c) Eugene Leonovich <gen.work@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phive\Queue\Tests\Queue;

use Phive\Queue\NoItemAvailableException;
use Phive\Queue\Queue;
use Phive\Queue\Tests\TimeUtils;

abstract class QueueTest extends \PHPUnit_Framework_TestCase
{
    use Util;

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

    public function testImplementQueueInterface()
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

    public function testPopDelay()
    {
        $eta = time() + 3;

        $this->queue->push('item', $eta);
        $this->assertNoItemIsAvailable($this->queue);

        TimeUtils::callAt($eta, function () {
            $this->assertEquals('item', $this->queue->pop());
        }, !$this->supportsExpiredEta);
    }

    public function testPushWithExpiredEta()
    {
        $this->queue->push('item', time() - 1);
        $this->assertEquals('item', $this->queue->pop());
    }

    public function testPushEqualItems()
    {
        $this->queue->push('item');
        $this->queue->push('item');

        $this->assertEquals('item', $this->queue->pop());
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
    public function testSupportItemType($item, $type)
    {
        $this->queue->push($item);

        if (Types::TYPE_BINARY_STRING === $type) {
            // strict comparison
            $this->assertSame($item, $this->queue->pop());
        } else {
            // loose comparison
            $this->assertEquals($item, $this->queue->pop());
        }
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
