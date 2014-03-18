<?php

namespace Phive\Queue\Tests\Handler;

use Pheanstalk_Exception_ServerException as ServerException;
use Pheanstalk_Pheanstalk as Pheanstalk;
use Phive\Queue\Queue\BeanstalkQueue;

class BeanstalkHandler extends Handler
{
    private $client;

    public function createQueue()
    {
        return new BeanstalkQueue($this->getClient(), $this->getOption('tube_name'));
    }

    public function clear()
    {
        $client = $this->getClient();
        $tubeName = $this->getOption('tube_name');

        self::doClear($client, $tubeName, 'ready');
        self::doClear($client, $tubeName, 'buried');
        self::doClear($client, $tubeName, 'delayed');
    }

    protected function getClient()
    {
        if (null === $this->client) {
            $this->client = new Pheanstalk($this->getOption('host'), $this->getOption('port'));
        }

        return $this->client;
    }

    private static function doClear($client, $tubeName, $state)
    {
        try {
            while ($item = $client->{'peek'.$state}($tubeName)) {
                $client->delete($item);
            }
        } catch (ServerException $e) {
        }
    }
}
