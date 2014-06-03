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

    public function provideItemsOfVariousTypes()
    {
        return array_diff_key(parent::provideItemsOfVariousTypes(), [
            'null'      => false,
            'bool'      => false,
            'int'       => false,
            'float'     => false,
            'array'     => false,
            'object'    => false,

        ]);
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
