<?php

namespace Phive\Queue\Tests\Queue;

use Phive\Queue\Queue\InMemoryQueue;

class InMemoryQueueTest extends AbstractQueueTest
{
    use PerformanceTrait;

    public function createQueue()
    {
        return new InMemoryQueue();
    }
}
