<?php

namespace Phive\Queue;

class NoItemAvailableException extends QueueException
{
    public function __construct(Queue $queue, $message = null, $code = null, \Exception $previous = null)
    {
        parent::__construct($queue, $message ?: 'No items are available in the queue.', $code, $previous);
    }
}
