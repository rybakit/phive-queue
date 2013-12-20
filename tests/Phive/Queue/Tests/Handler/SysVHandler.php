<?php

namespace Phive\Queue\Tests\Handler;

use Phive\Queue\Queue\SysVQueue;

class SysVHandler extends AbstractHandler
{
    public function __construct(array $options = [])
    {
        if (!extension_loaded('sysvmsg')) {
            throw new \RuntimeException('The "sysvmsg" extension is not loaded.');
        }

        parent::__construct($options);
    }

    public function createQueue()
    {
        return new SysVQueue($this->getOption('key'));
    }

    public function reset()
    {
        $this->clear();
    }

    public function clear()
    {
        msg_remove_queue(msg_get_queue($this->getOption('key')));
    }
}
