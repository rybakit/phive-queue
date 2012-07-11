<?php

namespace Phive\Tests\Queue\Redis;

use Phive\Tests\Queue\HandlerAwareQueueTestCase;
use Phive\Queue\Redis\RedisQueue;

class RedisQueueTest extends HandlerAwareQueueTestCase
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
