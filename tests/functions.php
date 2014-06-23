<?php

namespace Phive\Queue\Tests;

function freeze_time($timestamp)
{
    uopz_function('time', function () use ($timestamp) {
        return $timestamp;
    });

    uopz_function('DateTime', 'getTimestamp', function () use ($timestamp) {
        return $timestamp;
    });
}

function unfreeze_time()
{
    uopz_restore('time');
    uopz_restore('DateTime', 'getTimestamp');
}

function call_future(\Closure $func, $futureTime, $force_sleep = null)
{
    if (!function_exists('uopz_function')) {
        $force_sleep = true;
    }

    if ($force_sleep) {
        sleep(-time() + $futureTime);
        return $func();
    }

    freeze_time($futureTime);
    $result = $func();
    unfreeze_time();

    return $result;
}
