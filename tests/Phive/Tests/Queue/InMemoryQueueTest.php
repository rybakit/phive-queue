<?php

namespace Phive\Tests\Queue;

use Phive\Queue\InMemoryQueue;

class InMemoryQueueTest extends AbstractQueueTestCase
{
    public function createQueue()
    {
        return new InMemoryQueue();
    }
}
