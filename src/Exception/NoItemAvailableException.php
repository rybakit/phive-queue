<?php

namespace Phive\Queue\Exception;

class NoItemAvailableException extends RuntimeException
{
    public function __construct($message = null)
    {
        parent::__construct($message ?: 'No items are available in the queue.');
    }
}
