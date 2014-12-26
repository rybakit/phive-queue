<?php

namespace Phive\Queue\Tests\Queue;

use Phive\Queue\InMemoryQueue;

class InMemoryQueueTest extends QueueTest
{
    use Performance;

    public function createQueue()
    {
        return new InMemoryQueue();
    }
}
