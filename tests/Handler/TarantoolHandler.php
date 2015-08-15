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

use Phive\Queue\TarantoolQueue;

class TarantoolHandler extends Handler
{
    /**
     * @var \Tarantool
     */
    private $tarantool;

    public function createQueue()
    {
        return new TarantoolQueue(
            $this->tarantool,
            $this->getOption('tube_name'),
            $this->getOption('space')
        );
    }

    public function clear()
    {
        $this->tarantool->call('queue.truncate', [
            $this->getOption('space'),
            $this->getOption('tube_name'),
        ]);
    }

    protected function configure()
    {
        $this->tarantool = new \Tarantool($this->getOption('host'), $this->getOption('port'));
    }
}
