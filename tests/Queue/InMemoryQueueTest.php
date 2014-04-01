<?php

namespace Phive\Queue\Tests\Queue;

use Phive\Queue\InMemoryQueue;

class InMemoryQueueTest extends QueueTest
{
    use PerformanceTrait;

    public function createQueue()
    {
        return new InMemoryQueue();
    }
}
