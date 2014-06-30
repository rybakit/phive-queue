<?php

namespace Phive\Queue;

use Pheanstalk_Exception_ServerException as ServerException;
use Pheanstalk_PheanstalkInterface as Pheanstalk;

class PheanstalkQueue implements Queue
{
    /**
     * @var Pheanstalk
     */
    private $pheanstalk;

    /**
     * @var string
     */
    private $tubeName;

    public function __construct(Pheanstalk $pheanstalk, $tubeName)
    {
        $this->pheanstalk = $pheanstalk;
        $this->tubeName = $tubeName;
    }

    /**
     * {@inheritdoc}
     */
    public function push($item, $eta = null)
    {
        $this->pheanstalk->putInTube(
            $this->tubeName,
            $item,
            Pheanstalk::DEFAULT_PRIORITY,
            calc_delay($eta)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function pop()
    {
        if (!$item = $this->pheanstalk->reserveFromTube($this->tubeName, 0)) {
            throw new NoItemAvailableException($this);
        }

        $this->pheanstalk->delete($item);

        return $item->getData();
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        $stats = $this->pheanstalk->statsTube($this->tubeName);

        return $stats['current-jobs-ready'];
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->doClear('ready');
        $this->doClear('buried');
        $this->doClear('delayed');
    }

    protected function doClear($state)
    {
        try {
            while ($item = $this->pheanstalk->{'peek'.$state}($this->tubeName)) {
                $this->pheanstalk->delete($item);
            }
        } catch (ServerException $e) {
        }
    }
}
