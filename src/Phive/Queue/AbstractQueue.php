<?php

namespace Phive\Queue;

abstract class AbstractQueue
{
    protected function normalizeDate($date)
    {
        if (is_string($date)) {
            $date = new \DateTime($date);
        } else if (is_numeric($date)) {
            $date = new \DateTime('@'.$date);
        } else if (!$date instanceof \DateTime) {
            throw new \InvalidArgumentException(
                '$date must be a string, integer or \DateTime instance.'
            );
        }

        return $date;
    }
}