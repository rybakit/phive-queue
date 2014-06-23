<?php

namespace Phive\Queue\Tests\Queue;

use Phive\Queue as q;
use Phive\Queue\Tests as t;

class FunctionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var int Timestamp that will be returned by time().
     */
    private static $now = 1000000000;

    public static function setUpBeforeClass()
    {
        t\freeze_time(self::$now);
    }

    public static function tearDownAfterClass()
    {
        t\unfreeze_time();
    }

    /**
     * @dataProvider provideValidEtas
     */
    public function testNormEta($eta, $timestamp)
    {
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

        return [
            [0, 0, 0],
            [-1, -1, 0],
            [null, self::$now, 0],
            [self::$now, self::$now, 0],
            ['@'.self::$now, self::$now, 0],
            [$date->format(\DateTime::ISO8601), self::$now, 0],
            ['+1 hour', self::$now, 0],
            [$date, self::$now, 0],
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

    public function provideValidEtasWithDelay()
    {
        $date = new \DateTime();

        return [
            [0, -self::$now],
            [-1, -1],
            [null, self::$now],
            [self::$now, self::$now],
            ['@'.self::$now, self::$now],
            [$date->format(\DateTime::ISO8601), self::$now],
            ['+1 hour', self::$now],
            [$date, self::$now],
        ];
    }
}
