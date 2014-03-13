<?php

namespace Phive\Queue\Tests\Queue;

use Phive\Queue\Tests\Handler\BeanstalkHandler;

class BeanstalkQueueTest extends AbstractQueueTest
{
    use PerformanceTrait;
    use ConcurrencyTrait;

    protected $supportsExpiredEta = false;

    public static function createHandler(array $config)
    {
        return new BeanstalkHandler([
            'host' => $config['PHIVE_BEANSTALK_HOST'],
            'port' => $config['PHIVE_BEANSTALK_PORT'],
            'tube' => $config['PHIVE_BEANSTALK_TUBE'],
        ]);
    }
}
