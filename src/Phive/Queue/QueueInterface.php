<?php

namespace Phive\Queue;

interface QueueInterface extends \Countable
{
    /**
     * @param mixed                     $item
     * @param \DateTime|string|int|null $eta
     */
    public function push($item, $eta = null);

    /**
     * @return mixed|bool false if queue is empty, an item otherwise
     */
    public function pop();

    /**
     * @param int $limit
     * @param int $skip
     *
     * @return \Iterator
     *
     * @throws \OutOfRangeException
     */
    public function peek($limit = 1, $skip = 0);

    /**
     * Removes all items from the queue.
     */
    public function clear();
}
