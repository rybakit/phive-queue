<?php

namespace Phive\Queue;

use Phive\Queue\Exception\InvalidArgumentException;

class QueueUtils
{
    private function __construct()
    {
    }

    /**
     * @param mixed $eta
     *
     * @return int The Unix timestamp.
     *
     * @throws InvalidArgumentException
     */
    public static function normalizeEta($eta)
    {
        if (null === $eta) {
            return time();
        }
        if (is_string($eta)) {
            $eta = date_create($eta);
        }
        if ($eta instanceof \DateTime) {
            return $eta->getTimestamp();
        }
        if (is_int($eta)) {
            return $eta;
        }

        throw new InvalidArgumentException('The eta parameter is not valid.');
    }
}
