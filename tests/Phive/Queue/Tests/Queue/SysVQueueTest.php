<?php

namespace Phive\Queue\Tests\Queue;

use Phive\Queue\Queue\SysVQueue;

class SysVQueueTest extends AbstractQueueTest
{
    public function createQueue()
    {
        if (!extension_loaded('sysvmsg')) {
            $this->markTestSkipped('The "sysvmsg" extension is not loaded.');
        }

        return new SysVQueue(0xDEADBEEF);
    }
}
