<?php

namespace Phive\Queue\Tests\Queue;

use Phive\Queue\Tests\Handler\PheanstalkHandler;

class PheanstalkQueueTest extends QueueTest
{
    use PerformanceTrait;
    use ConcurrencyTrait;

    protected $supportsExpiredEta = false;

    protected function getUnsupportedItemTypes()
    {
        return [Types::TYPE_ARRAY, Types::TYPE_OBJECT];
    }

    /**
     * @dataProvider provideItemsOfUnsupportedTypes
     * @expectedException PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage expects parameter 1 to be string
     */
    public function testUnsupportedItemType($item)
    {
        $this->queue->push($item);
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
