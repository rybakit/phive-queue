<?php

namespace Phive\Tests\Queue;

use Phive\Queue\InMemoryQueue;

class InMemoryQueueTest extends AbstractQueueTest
{
    public function createQueue()
    {
        return new InMemoryQueue();
    }
}
