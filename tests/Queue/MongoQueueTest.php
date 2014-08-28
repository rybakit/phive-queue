<?php

namespace Phive\Queue\Tests\Queue;

use Phive\Queue\Tests\Handler\MongoHandler;

/**
 * @requires extension mongo
 */
class MongoQueueTest extends QueueTest
{
    use PerformanceTrait;
    use ConcurrencyTrait;

    public function getUnsupportedItemTypes()
    {
        return ['object'];
    }

    /**
     * @dataProvider provideItemsOfUnsupportedTypes
     * @expectedException \MongoException
     * @expectedExceptionMessage zero-length keys are not allowed, did you use $ with double quotes?
     */
    public function testGetErrorOnUnsupportedItemType($item)
    {
        $this->queue->push($item);
    }

    public static function createHandler(array $config)
    {
        return new MongoHandler([
            'server'    => $config['PHIVE_MONGO_SERVER'],
            'db_name'   => $config['PHIVE_MONGO_DB_NAME'],
            'coll_name' => $config['PHIVE_MONGO_COLL_NAME'],
        ]);
    }
}
