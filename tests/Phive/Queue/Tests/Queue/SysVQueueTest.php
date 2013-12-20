<?php

namespace Phive\Queue\Tests\Queue;

use Phive\Queue\Tests\Handler\SysVHandler;

class SysVQueueTest extends AbstractPersistentQueueTest
{
    public static function createHandler()
    {
        return new SysVHandler(['key' => 0xDEADBEEF]);
    }
}
