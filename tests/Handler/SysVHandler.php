<?php

/*
 * This file is part of the Phive Queue package.
 *
 * (c) Eugene Leonovich <gen.work@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phive\Queue\Tests\Handler;

use Phive\Queue\SysVQueue;

class SysVHandler extends Handler
{
    public function createQueue()
    {
        return new SysVQueue($this->getOption('key'));
    }

    public function clear()
    {
        msg_remove_queue(msg_get_queue($this->getOption('key')));
    }

    public function getMeta()
    {
        return msg_stat_queue(msg_get_queue($this->getOption('key')));
    }
}
