<?php

namespace Phive\Queue\Tests\Queue;

use Phive\Queue\Tests\Handler\RedisHandler;

/**
 * @requires extension redis
 */
class RedisQueueTest extends AbstractPersistentQueueTest
{
    public static function createHandler(array $config)
    {
        return new RedisHandler([
            'host'   => $config['redis_host'],
            'port'   => $config['redis_port'],
            'prefix' => $config['redis_prefix'],
        ]);
    }
}
