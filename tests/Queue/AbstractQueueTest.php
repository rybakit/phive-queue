<?php

namespace Phive\Queue\Tests\Queue;

use Phive\Queue\Exception\NoItemAvailableException;
use Phive\Queue\Queue\QueueInterface;

abstract class AbstractQueueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var int Timestamp that will be returned by time().
     */
    public static $now;

    /**
     * @var \Phive\Queue\Queue\QueueInterface
     */
    protected $queue;

    /**
     * Whether the queue supports an expired ETA or not.
     *
     * @var bool
     */
    protected $supportsExpiredEta = true;

    public function testQueueImplementsQueueInterface()
    {
        $this->assertInstanceOf('Phive\Queue\Queue\QueueInterface', $this->queue);
    }

    public function testPushPop()
    {
        $this->queue->push('item');

        $this->assertEquals('item', $this->queue->pop());
        $this->assertNoItemAvailableException($this->queue);
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
        $this->assertNoItemAvailableException($this->queue);

        $this->callInFuture(function () {
            $this->assertEquals('item', $this->queue->pop());
        }, $eta, !$this->supportsExpiredEta);
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

    protected function setUp()
    {
        $this->queue = $this->createQueue();

        self::$now = null;
        $this->stubTimeFunction();
    }

    protected function assertNoItemAvailableException(QueueInterface $queue)
    {
        try {
            $queue->pop();
        } catch (NoItemAvailableException $e) {
            return;
        }

        $this->fail('An expected NoItemAvailableException has not been raised.');
    }

    protected function callInFuture(\Closure $func, $futureTime, $sleep = false)
    {
        if ($sleep) {
            sleep($futureTime - time());
            return $func($this);
        }

        self::$now = $futureTime;
        $result = $func();
        self::$now = null;

        return $result;
    }

    private function stubTimeFunction()
    {
        $class = get_class($this->queue);
        $namespace = substr($class, 0, strrpos($class, '\\'));

        // this code should be evaluated directly after the queue class is loaded
        // and before any queue method is called
        if (!is_callable("$namespace\\time")) {
            eval('namespace '.$namespace.' { function time() { return \\'.__CLASS__.'::$now ?: \time(); }}');
        }
    }

    /**
     * @return \Phive\Queue\Queue\QueueInterface
     */
    abstract public function createQueue();
}
