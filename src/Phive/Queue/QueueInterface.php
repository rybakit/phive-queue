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
     * @param int $offset
     * @param int $limit
     *
     * @return \Iterator
     *
     * @throws \InvalidArgumentException|\OutOfRangeException
     */
    public function slice($offset, $limit);

    /**
     * Removes all items from the queue.
     */
    public function clear();
}
