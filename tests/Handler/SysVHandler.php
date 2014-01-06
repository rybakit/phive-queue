<?php

namespace Phive\Queue\Tests\Handler;

use Phive\Queue\Queue\SysVQueue;

class SysVHandler extends AbstractHandler
{
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
