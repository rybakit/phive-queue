<?php

/*
 * This file is part of the Phive Queue package.
 *
 * (c) Eugene Leonovich <gen.work@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phive\Queue\Tests\Handler;

use Phive\Queue\RedisQueue;

class RedisHandler extends Handler
{
    /**
     * @var \Redis
     */
    private $redis;

    public function createQueue()
    {
        return new RedisQueue($this->redis);
    }

    public function clear()
    {
        $this->redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_NONE);
        $prefix = $this->redis->getOption(\Redis::OPT_PREFIX);
        $offset = strlen($prefix);

        $keys = $this->redis->keys('*');
        foreach ($keys as $key) {
            $this->redis->del(substr($key, $offset));
        }
    }

    public function createRedis()
    {
        $redis = new \Redis();
        $redis->connect($this->getOption('host'), $this->getOption('port'));
        $redis->setOption(\Redis::OPT_PREFIX, $this->getOption('prefix'));

        return $redis;
    }

    protected function configure()
    {
        $this->redis = $this->createRedis();
    }
}
