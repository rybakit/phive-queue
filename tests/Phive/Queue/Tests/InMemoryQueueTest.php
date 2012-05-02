<?php

namespace Phive\Queue\Tests;

use Phive\Queue\InMemoryQueue;

class InMemoryQueueTest extends AbstractQueueTest
{
    protected function createQueue()
    {
        return new InMemoryQueue();
    }
}