<?php

namespace Phive\Queue\Tests\Queue;

use Phive\Queue\Tests\Handler\SysVHandler;

/**
 * @requires extension sysvmsg
 */
class SysVQueueTest extends AbstractQueueTest
{
    use PerformanceTrait {
        PerformanceTrait::testPushPopPerformance as baseTestPushPopPerformance;
    }
    use ConcurrencyTrait;

    public static function createHandler(array $config)
    {
        return new SysVHandler([
            'key' => $config['PHIVE_SYSV_KEY'],
        ]);
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
        $queueSize = $this->getPerformanceQueueSize();

        if ($this->getPerformanceItemLength() * $queueSize > $maxSize) {
            $this->markTestSkipped(
                "The System V queue size is too small ($maxSize bytes) to run this test. ".
                'Try to decrease the "performance_queue_size" value to '.floor($maxSize / $this->getPerformanceItemLength()).' in your phpunit.xml.'
            );
        }

        self::baseTestPushPopPerformance();
    }
}
