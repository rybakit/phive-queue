<?php

namespace Phive\Queue\Tests\Queue;

use Phive\Queue\NoItemAvailableException;
use Phive\Queue\QueueException;
use Phive\Queue\SysVQueue;
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

    public function testSetPermissions()
    {
        $handler = self::getHandler();
        $key = $handler->getOption('key');

        $queue = new SysVQueue($key, null, 0606);

        // force a resource creation
        $queue->count();

        $info = self::getResourceInfo();

        $this->assertEquals('606', $info['perms']);
    }

    public function testSetItemMaxLength()
    {
        $this->queue->push('xx');
        $this->queue->setItemMaxLength(1);

        try {
            $this->queue->pop();
        } catch (\Exception $e) {
            if (7 === $e->getCode() && 'Argument list too long' === $e->getMessage()) {
                return;
            }
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
        self::exec('ipcrm -Q '.self::getResourceKey());
    }

    private static function getResourceInfo()
    {
        $result = self::exec('ipcs -q');

        $count = count($result);
        if ($count < 4) {
            throw new \UnexpectedValueException('No queues were found.');
        }

        $key = self::getResourceKey();

        for ($i = 3; $i < $count; $i++) {
            if (0 === strpos($result[$i], $key)) {
                return array_combine(
                    preg_split('/\s+/', $result[2]), // key, msqid, owner, perms, used_bytes, messages
                    preg_split('/\s+/', $result[$i])
                );
            }
        }

        throw new \UnexpectedValueException(sprintf('A queue with the key "%s" was not found.', $key));
    }

    private static function getResourceKey()
    {
        $key = self::getHandler()->getOption('key');

        return '0x'.dechex($key);
    }

    private static function exec($command)
    {
        exec($command, $result, $status);

        if (0 !== $status) {
            throw new \RuntimeException(sprintf('En error occurs while executing "%s": %s.', $command, implode("\n", $result)));
        }

        return $result;
    }
}
