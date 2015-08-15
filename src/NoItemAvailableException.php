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

class NoItemAvailableException extends QueueException
{
    public function __construct(Queue $queue, $message = null, $code = null, \Exception $previous = null)
    {
        parent::__construct($queue, $message ?: 'No items are available in the queue.', $code, $previous);
    }
}
