<?php

namespace Phive\Queue\Tests\Queue;

use Phive\Queue\NoItemAvailableException;
use Phive\Queue\QueueException;
use Phive\Queue\Tests\Handler\SysVHandler;

/**
 * @requires extension sysvmsg
 */
class SysVQueueTest extends QueueTest
{
    use PerformanceTrait {
        PerformanceTrait::testPushPopPerformance as baseTestPushPopPerformance;
    }
    use ConcurrencyTrait;
    use UtilTrait;

    /**
     * @dataProvider provideQueueInterfaceMethods
     */
    public function testQueueThrowsExceptionIfResourceIsNotExist($method)
    {
        // force a resource creation
        $this->queue->count();

        self::removeResource();

        try {
            $this->callQueueMethod($this->queue, $method);
        } catch (NoItemAvailableException $e) {
        } catch (QueueException $e) {
            return;
        }

        $this->fail();
    }

    /**
     * @group performance
     * @dataProvider providePerformanceData
     */
    public function testPushPopPerformance($benchmarkMethod, $delay)
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

        self::baseTestPushPopPerformance($benchmarkMethod, $delay);
    }

    public function provideItemsOfVariousTypes()
    {
        return array_diff_key(parent::provideItemsOfVariousTypes(), [
            'null'      => false,
            'array'     => false,
            'object'    => false,
        ]);
    }

    public static function createHandler(array $config)
    {
        return new SysVHandler([
            'key' => $config['PHIVE_SYSV_KEY'],
        ]);
    }

    private static function removeResource()
    {
        $key = self::getHandler()->getOption('key');
        $key = '0x'.dechex($key);

        exec('ipcs -q', $output);

        $count = count($output);
        if ($count < 4) {
            return;
        }

        for ($i = 3; $i < $count; $i++) {
            if (0 === strpos($output[$i], $key)) {
                exec('ipcrm -Q '.$key, $output);
                break;
            }
        }
    }
}
