<?php

namespace Phive\Tests\Queue\MongoDb;

use Phive\Tests\Queue\HandlerAwareQueueTestCase;
use Phive\Queue\MongoDb\MongoDbQueue;

class MongoDbQueueTest extends HandlerAwareQueueTestCase
{
    public static function createHandler()
    {
        return new MongoDbHandler(array(
            'server'    => $GLOBALS['mongo_server'],
            'db_name'   => $GLOBALS['mongo_db_name'],
            'coll_name' => $GLOBALS['mongo_coll_name'],
        ));
    }
}
