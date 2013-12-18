<?php

namespace Phive\Queue\Tests\Queue;

use Phive\Queue\Queue\SysVQueue;

class SysVQueueTest extends AbstractQueueTest
{
    public function createQueue()
    {
        return new SysVQueue(0xDEADBEEF);
    }
}
