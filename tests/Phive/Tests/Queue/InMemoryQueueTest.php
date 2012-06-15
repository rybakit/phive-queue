<?php

namespace Phive\Tests\Queue;

use Phive\Queue\InMemoryQueue;

class InMemoryQueueTest extends AbstractQueueTestCase
{
    protected function createQueue()
    {
        return new InMemoryQueue();
    }
}
