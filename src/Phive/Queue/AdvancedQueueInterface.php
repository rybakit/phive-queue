<?php

namespace Phive\Queue;

interface AdvancedQueueInterface extends QueueInterface, \Countable
{
    /**
     * @param int $limit
     * @param int $skip
     *
     * @throws \OutOfRangeException
     *
     * @return \Iterator
     */
    function peek($limit = 1, $skip = 0);

    /**
     * Removes all tasks from the queue.
     */
    function clear();
}