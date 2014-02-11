<?php

namespace Phive\Queue\Queue;

interface QueueInterface extends \Countable
{
    /**
     * @param string $item
     * @param mixed  $eta
     */
    public function push($item, $eta = null);

    /**
     * @return string
     *
     * @throws \Phive\Queue\Exception\NoItemAvailableException
     */
    public function pop();

    /**
     * Removes all items from the queue.
     */
    public function clear();
}
