<?php

namespace Phive\Queue\Tests\Queue;

use Phive\Queue\Tests\Handler\SysVHandler;

/**
 * @requires extension sysvmsg
 */
class SysVQueueTest extends AbstractPersistentQueueTest
{
    public static function createHandler(array $config)
    {
        return new SysVHandler(array('key' => $config['sysv_key']));
    }
}
