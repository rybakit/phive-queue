<?php

namespace Phive\Queue;

abstract class AbstractQueue
{
    protected function normalizeEta($eta)
    {
        if (is_string($eta)) {
            $eta = new \DateTime($eta);
        }
        if ($eta instanceof \DateTime) {
            return $eta->getTimestamp();
        }
        if (is_numeric($eta)) {
            return $eta;
        }

        throw new \InvalidArgumentException('$eta must be a string, integer or \DateTime instance.');
    }
}