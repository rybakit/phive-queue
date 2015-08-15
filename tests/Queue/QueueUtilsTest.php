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

use Phive\Queue\QueueUtils;

class QueueUtilsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideValidEtas
     */
    public function testNormalizeEta($eta, $timestamp)
    {
        if (is_callable($timestamp)) {
            $timestamp = $timestamp();
        }

        $this->assertEquals($timestamp, QueueUtils::normalizeEta($eta));
    }

    /**
     * @dataProvider provideInvalidEtas
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The eta parameter is not valid.
     */
    public function testNormalizeEtaThrowsException($eta)
    {
        QueueUtils::normalizeEta($eta);
    }

    /**
     * @dataProvider provideValidEtas
     */
    public function testCalcDelay($eta, $_, $delay)
    {
        $this->assertEquals($delay, QueueUtils::calculateDelay($eta));
    }

    public function provideValidEtas()
    {
        $date = new \DateTime();
        $now = $date->getTimestamp();

        return [
            [0, 0, 0],
            [-1, -1, 0],
            [null, function () { return time(); }, 0],
            [$now, $now, 0],
            ['@'.$now, $now, 0],
            [$date->format(\DateTime::ISO8601), $now, 0],
            ['+1 hour', function () { return time() + 3600; }, 3600],
            [$date, $now, 0],
        ];
    }

    public function provideInvalidEtas()
    {
        return [
            [new \stdClass()],
            ['invalid eta string'],
            [[]],
        ];
    }
}
