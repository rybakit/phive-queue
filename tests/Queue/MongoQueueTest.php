<?php

namespace Phive\Queue\Tests\Queue;

use Phive\Queue\Tests\Handler\MongoHandler;

/**
 * @requires extension mongo
 */
class MongoQueueTest extends AbstractPersistentQueueTest
{
    public static function createHandler(array $config)
    {
        return new MongoHandler([
            'server'    => $config['mongo_server'],
            'db_name'   => $config['mongo_db_name'],
            'coll_name' => $config['mongo_coll_name'],
        ]);
    }

}
