<?php

namespace Phive\Tests\Queue;

abstract class HandlerAwareQueueTestCase extends QueueTestCase
{
    /**
     * @var AbstractHandler
     */
    protected static $handler;

    public static function setUpBeforeClass()
    {
        try {
            static::$handler = static::createHandler();
        } catch (\Exception $e) {
            static::markTestSkipped($e->getMessage());
        }

        parent::setUpBeforeClass();
    }

    public function setUp()
    {
        parent::setUp();

        static::$handler->reset();
    }

    /**
     * @return \Phive\Queue\QueueInterface
     *
     * @throws \LogicException
     */
    public function createQueue()
    {
        return static::$handler->createQueue();
    }

    abstract public static function createHandler();
}
