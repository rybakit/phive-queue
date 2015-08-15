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

use Pheanstalk\Exception\ServerException;
use Pheanstalk\Pheanstalk;
use Phive\Queue\PheanstalkQueue;

class PheanstalkHandler extends Handler
{
    /**
     * @var \Pheanstalk\PheanstalkInterface
     */
    private $pheanstalk;

    public function createQueue()
    {
        return new PheanstalkQueue($this->pheanstalk, $this->getOption('tube_name'));
    }

    public function clear()
    {
        $tubeName = $this->getOption('tube_name');

        $this->doClear($tubeName, 'ready');
        $this->doClear($tubeName, 'buried');
        $this->doClear($tubeName, 'delayed');
    }

    protected function configure()
    {
        $this->pheanstalk = new Pheanstalk($this->getOption('host'), $this->getOption('port'));
    }

    private function doClear($tubeName, $state)
    {
        try {
            while ($item = $this->pheanstalk->{'peek'.$state}($tubeName)) {
                $this->pheanstalk->delete($item);
            }
        } catch (ServerException $e) {
        }
    }
}
