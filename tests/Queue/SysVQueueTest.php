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

        $maxSizeInBytes = (int) str_replace('kernel.msgmnb = ', '', $output[0]);
        $queueSize = $this->getPerformanceQueueSize();

        if (static::$performanceItemLength * $queueSize > $maxSizeInBytes) {
            $this->markTestSkipped(sprintf(
                'The System V queue size is too small (%d bytes) to run this test. '.
                'Try to decrease the "PHIVE_PERF_QUEUE_SIZE" environment variable to %d.',
                $maxSizeInBytes,
                floor($maxSizeInBytes / static::$performanceItemLength)
            ));
        }

        self::baseTestPushPopPerformance();
    }
}
