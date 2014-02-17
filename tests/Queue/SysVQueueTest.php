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
        return new SysVHandler(['key' => $config['sysv_key']]);
    }

    /**
     * @group performance
     */
    public function testPushPopPerformance()
    {
        exec('sysctl kernel.msgmnb 2> /dev/null', $output);

        if (!$output) {
            $this->markTestSkipped('Unable to determine the max size of the System V queue.');
        }

        $maxSize = (int) str_replace('kernel.msgmnb = ', '', $output[0]);
        $queueSize = (int) $GLOBALS['performance_queue_size'];

        if (self::PERF_ITEM_LENGTH * $queueSize > $maxSize) {
            $this->markTestSkipped(
                "The System V queue size is too small ($maxSize bytes) to run this test. ".
                'Try to decrease the "performance_queue_size" value to '.floor($maxSize / self::PERF_ITEM_LENGTH).' in your phpunit.xml.'
            );
        }

        parent::testPushPopPerformance();
    }
}
