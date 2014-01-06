<?php

namespace Phive\Queue\Tests\Queue;

use Phive\Queue\Queue\InMemoryQueue;

class InMemoryQueueTest extends AbstractQueueTest
{
    public function createQueue()
    {
        return new InMemoryQueue();
    }
}
