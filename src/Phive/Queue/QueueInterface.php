<?php

namespace Phive\Queue;

interface QueueInterface
{
    /**
     * @param mixed $item
     * @param \DateTime|string|int|null $eta
     */
    function push($item, $eta = null);

    /**
     * @return mixed|bool false if queue is empty, an item otherwise
     */
    function pop();
}