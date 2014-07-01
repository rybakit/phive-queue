<?php

namespace Phive\Queue\Tests\Queue;

use Phive\Queue\Tests\Handler\PredisHandler;

class PredisQueueTest extends QueueTest
{
    use PerformanceTrait;
    use ConcurrencyTrait;

    public function getUnsupportedItemTypes()
    {
        return ['array', 'object'];
    }
    
    public static function createHandler(array $config)
    {
        return new PredisHandler([
            'host'   => $config['PHIVE_REDIS_HOST'],
            'port'   => $config['PHIVE_REDIS_PORT'],
            'prefix' => $config['PHIVE_REDIS_PREFIX'],
        ]);
    }
}
