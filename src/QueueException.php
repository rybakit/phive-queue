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
