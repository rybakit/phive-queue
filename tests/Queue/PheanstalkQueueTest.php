<?php

namespace Phive\Queue\Tests\Queue;

use Phive\Queue\Tests\Handler\PheanstalkHandler;

class PheanstalkQueueTest extends QueueTest
{
    use PerformanceTrait;
    use ConcurrencyTrait;

    protected $supportsExpiredEta = false;

    public function getUnsupportedItemTypes()
    {
        return ['array', 'object'];
    }

    public static function createHandler(array $config)
    {
        return new PheanstalkHandler([
            'host'      => $config['PHIVE_BEANSTALK_HOST'],
            'port'      => $config['PHIVE_BEANSTALK_PORT'],
            'tube_name' => $config['PHIVE_BEANSTALK_TUBE_NAME'],
        ]);
    }
}
