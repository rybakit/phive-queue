<?php

namespace Phive\Tests\Queue;

use Phive\Queue\InMemoryQueue;

class InMemoryQueueTest extends AbstractQueueTest
{
    protected function createQueue()
    {
        return new InMemoryQueue();
    }
}
