<?php

namespace Phive\Queue\Tests\Queue;

use Phive\Queue\Tests\Handler\TarantoolHandler;

/**
 * @requires extension tarantool
 */
class TarantoolQueueTest extends QueueTest
{
    use PerformanceTrait;
    use ConcurrencyTrait;

    protected $supportsExpiredEta = false;

    public function getUnsupportedItemTypes()
    {
        return [Types::TYPE_ARRAY, Types::TYPE_OBJECT];
    }

    /**
     * @dataProvider provideItemsOfUnsupportedTypes
     * @expectedException \Exception
     * @expectedExceptionMessage /(could not be converted to string)|(Array to string conversion|unsupported field type)/
     */
    public function testUnsupportedItemType($item)
    {
        $this->queue->push($item);
    }

    /**
     * @see https://github.com/tarantool/tarantool/issues/336
     */
    public function testItemsOfDifferentLength()
    {
        for ($item = 'x'; strlen($item) < 9; $item .= 'x') {
            $this->queue->push($item);
            $this->assertEquals($item, $this->queue->pop());
        }
    }

    public static function createHandler(array $config)
    {
        return new TarantoolHandler([
            'host'      => $config['PHIVE_TARANTOOL_HOST'],
            'port'      => $config['PHIVE_TARANTOOL_PORT'],
            'space'     => $config['PHIVE_TARANTOOL_SPACE'],
            'tube_name' => $config['PHIVE_TARANTOOL_TUBE_NAME'],
        ]);
    }
}
