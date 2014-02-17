<?php

namespace Phive\Queue\Tests\Queue;

use Phive\Queue\Tests\Handler\MongoHandler;

/**
 * @requires extension mongo
 */
class MongoQueueTest extends AbstractQueueTest
{
    use PerformanceTrait;
    use ConcurrencyTrait;

    public static function createHandler(array $config)
    {
        return new MongoHandler([
            'server'    => $config['PHIVE_MONGO_SERVER'],
            'db_name'   => $config['PHIVE_MONGO_DB_NAME'],
            'coll_name' => $config['PHIVE_MONGO_COLL_NAME'],
        ]);
    }

}
