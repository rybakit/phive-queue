<?php

namespace Phive\Queue\Tests\Queue;

use Phive\Queue as q;

class UtilsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideValidEtas
     */
    public function testNormEta($eta, $timestamp)
    {
        if (is_callable($timestamp)) {
            $timestamp = $timestamp();
        }

        $this->assertEquals($timestamp, q\norm_eta($eta));

    }

    /**
     * @dataProvider provideInvalidEtas
     * @expectedException \InvalidArgumentException
     */
    public function testNormEtaThrowsException($eta)
    {
        q\norm_eta($eta);
    }

    /**
     * @dataProvider provideValidEtas
     */
    public function testCalcDelay($eta, $_, $delay)
    {
        $this->assertEquals($delay, q\calc_delay($eta));
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
