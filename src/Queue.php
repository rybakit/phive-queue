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

interface Queue extends \Countable
{
    /**
     * Adds an item to the queue.
     *
     * @param mixed $item An item to be added.
     * @param mixed $eta  The earliest time that an item can be popped.
     */
    public function push($item, $eta = null);

    /**
     * Removes an item from the queue and returns it.
     *
     * @return mixed
     *
     * @throws NoItemAvailableException
     */
    public function pop();

    /**
     * Removes all items from the queue.
     */
    public function clear();
}
