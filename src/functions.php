<?php

namespace Phive\Queue;

/**
 * @param mixed $eta
 *
 * @return int The Unix timestamp.
 *
 * @throws \InvalidArgumentException
 */
function norm_eta($eta)
{
    if (null === $eta) {
        return time();
    }
    if (is_string($eta)) {
        $eta = date_create($eta);
    }
    if ($eta instanceof \DateTime || $eta instanceof \DateTimeInterface) {
        return $eta->getTimestamp();
    }
    if (is_int($eta)) {
        return $eta;
    }

    throw new \InvalidArgumentException('The eta parameter is not valid.');
}

/**
 * @param mixed $eta
 *
 * @return int
 */
function calc_delay($eta)
{
    if (null === $eta) {
        return 0;
    }

    $delay = -time() + norm_eta($eta);

    return ($delay < 0) ? 0 : $delay;
}
