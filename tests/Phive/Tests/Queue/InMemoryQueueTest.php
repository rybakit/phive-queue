<?php

namespace Phive\Tests\Queue;

use Phive\Queue\InMemoryQueue;

class InMemoryQueueTest extends QueueTestCase
{
    public function createQueue()
    {
        return new InMemoryQueue();
    }
}
