<?php

namespace Phive\Queue;

class QueueUtils
{
    private function __construct()
    {
    }

    /**
     * @param \DateTime|string|int|null $eta
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
        if ($eta instanceof \DateTime) {
            return $eta->getTimestamp();
        }
        if (is_int($eta)) {
            return $eta;
        }

        throw new \InvalidArgumentException('Parameter "eta" must be a string, integer or \DateTime instance.');
    }

    /**
     * @param mixed $value
     *
     * @return int
     */
    public static function normalizeLimit($value)
    {
        return self::normalizeNumber($value, 'limit', 1);
    }

    /**
     * @param mixed $value
     *
     * @return int
     */
    public static function normalizeOffset($value)
    {
        return self::normalizeNumber($value, 'offset', 0);
    }

    /**
     * @param mixed  $number
     * @param string $name
     * @param int    $min
     *
     * @throws \InvalidArgumentException|\OutOfRangeException
     */
    private static function normalizeNumber($number, $name, $min)
    {
        if (!is_numeric($number)) {
            throw new \InvalidArgumentException("Parameter \"$name\" must be a valid number.");
        }

        if ($number < $min) {
            throw new \OutOfRangeException("Parameter \"$name\" must be $min or more.");
        }

        return (int) $number;
    }
}
