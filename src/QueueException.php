<?php

namespace Phive\Queue;

class QueueException extends \RuntimeException
{
    private $queue;

    public function __construct(Queue $queue, $message = null, $code = null, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->queue = $queue;
    }

    public function getQueue()
    {
        return $this->queue;
    }
}
