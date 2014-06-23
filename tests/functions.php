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
