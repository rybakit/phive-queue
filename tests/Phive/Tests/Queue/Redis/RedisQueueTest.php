<?php

namespace Phive\Tests\Queue\Redis;

use Phive\Tests\Queue\HandlerAwareQueueTest;
use Phive\Queue\Redis\RedisQueue;

class RedisQueueTest extends HandlerAwareQueueTest
{
    public static function createHandler()
    {
        return new RedisHandler(array(
            'host'   => $GLOBALS['redis_host'],
            'port'   => $GLOBALS['redis_port'],
            'prefix' => $GLOBALS['redis_prefix'],
        ));
    }
}
