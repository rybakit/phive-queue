<?php

/*
 * This file is part of the Phive Queue package.
 *
 * (c) Eugene Leonovich <gen.work@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phive\Queue;

abstract class QueueUtils
{
    /**
     * @param mixed $eta
     *
     * @return int The Unix timestamp.
     *
     * @throws \InvalidArgumentException
     */
    public static function normalizeEta($eta)
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
    public static function calculateDelay($eta)
    {
        if (null === $eta) {
            return 0;
        }

        $delay = -time() + self::normalizeEta($eta);

        return ($delay < 0) ? 0 : $delay;
    }
}
