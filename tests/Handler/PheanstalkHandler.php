<?php

namespace Phive\Queue\Tests\Handler;

use Pheanstalk_Exception_ServerException as ServerException;
use Pheanstalk_Pheanstalk as Pheanstalk;
use Phive\Queue\PheanstalkQueue;

class PheanstalkHandler extends Handler
{
    private $pheanstalk;

    public function createQueue()
    {
        return new PheanstalkQueue($this->getPheanstalk(), $this->getOption('tube_name'));
    }

    public function clear()
    {
        $pheanstalk = $this->getPheanstalk();
        $tubeName = $this->getOption('tube_name');

        self::doClear($pheanstalk, $tubeName, 'ready');
        self::doClear($pheanstalk, $tubeName, 'buried');
        self::doClear($pheanstalk, $tubeName, 'delayed');
    }

    protected function getPheanstalk()
    {
        if (null === $this->pheanstalk) {
            $this->pheanstalk = new Pheanstalk($this->getOption('host'), $this->getOption('port'));
        }

        return $this->pheanstalk;
    }

    private static function doClear($pheanstalk, $tubeName, $state)
    {
        try {
            while ($item = $pheanstalk->{'peek'.$state}($tubeName)) {
                $pheanstalk->delete($item);
            }
        } catch (ServerException $e) {
        }
    }
}
