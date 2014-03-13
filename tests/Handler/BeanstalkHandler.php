<?php

namespace Phive\Queue\Tests\Handler;

use Pheanstalk_Exception_ServerException as ServerException;
use Pheanstalk_Pheanstalk as Pheanstalk;
use Phive\Queue\Queue\BeanstalkQueue;

class BeanstalkHandler extends AbstractHandler
{
    private $client;

    public function createQueue()
    {
        return new BeanstalkQueue($this->getClient(), $this->getOption('tube'));
    }

    public function clear()
    {
        $client = $this->getClient();
        $tube = $this->getOption('tube');

        self::doClear($client, $tube, 'ready');
        self::doClear($client, $tube, 'buried');
        self::doClear($client, $tube, 'delayed');
    }

    protected function getClient()
    {
        if (null === $this->client) {
            $this->client = new Pheanstalk($this->getOption('host'), $this->getOption('port'));
        }

        return $this->client;
    }

    private static function doClear($client, $tube, $state)
    {
        try {
            while ($item = $client->{'peek'.$state}($tube)) {
                $client->delete($item);
            }
        } catch (ServerException $e) {
        }
    }
}
