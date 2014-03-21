<?php

namespace Phive\Queue\Queue;

interface Queue extends \Countable
{
    /**
     * @param mixed $item
     * @param mixed $eta
     */
    public function push($item, $eta = null);

    /**
     * @return mixed
     *
     * @throws \Phive\Queue\Exception\NoItemAvailableException
     */
    public function pop();

    /**
     * Removes all items from the queue.
     */
    public function clear();
}
