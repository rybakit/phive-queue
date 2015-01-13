<?php

namespace Phive\Queue\Tests;

abstract class TimeUtils
{
    public static function callAt($timestamp, \Closure $func, $forceSleep = null)
    {
        if (!function_exists('uopz_function')) {
            $forceSleep = true;
        }

        if ($forceSleep) {
            sleep(-time() + $timestamp);

            return $func();
        }

        self::setTime($timestamp);
        $result = $func();
        self::unsetTime();

        return $result;
    }

    private static function setTime($timestamp)
    {
        $handler = function () use ($timestamp) {
            return $timestamp;
        };

        uopz_function('time', $handler);
        uopz_function('DateTime', 'getTimestamp', $handler);
    }

    private static function unsetTime()
    {
        uopz_restore('time');
        uopz_restore('DateTime', 'getTimestamp');
    }
}
