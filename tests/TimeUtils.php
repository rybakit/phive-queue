<?php

namespace Phive\Queue\Tests;

class TimeUtils
{
    public static function setTime($timestamp)
    {
        uopz_function('time', function () use ($timestamp) {
            return $timestamp;
        });

        uopz_function('DateTime', 'getTimestamp', function () use ($timestamp) {
            return $timestamp;
        });
    }

    public static function unsetTime()
    {
        uopz_restore('time');
        uopz_restore('DateTime', 'getTimestamp');
    }

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
}
