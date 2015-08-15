<?php

/*
 * This file is part of the Phive Queue package.
 *
 * (c) Eugene Leonovich <gen.work@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phive\Queue\Tests\Queue;

use Phive\Queue\Tests\Handler\MongoHandler;

/**
 * @requires function MongoClient::connect
 */
class MongoQueueTest extends QueueTest
{
    use Performance;
    use Concurrency;

    protected function getUnsupportedItemTypes()
    {
        return [Types::TYPE_BINARY_STRING, Types::TYPE_OBJECT];
    }

    /**
     * @dataProvider provideItemsOfUnsupportedTypes
     * @expectedException Exception
     * @expectedExceptionMessageRegExp /zero-length keys are not allowed|non-utf8 string|Objects are not identical/
     */
    public function testUnsupportedItemType($item, $type)
    {
        $this->queue->push($item);

        if (Types::TYPE_OBJECT === $type && $item !== $this->queue->pop()) {
            throw new \Exception('Objects are not identical');
        }
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
