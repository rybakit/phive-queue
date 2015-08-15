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

use Pheanstalk\Exception\ServerException;
use Pheanstalk\PheanstalkInterface;

class PheanstalkQueue implements Queue
{
    /**
     * @var PheanstalkInterface
     */
    private $pheanstalk;

    /**
     * @var string
     */
    private $tubeName;

    public function __construct(PheanstalkInterface $pheanstalk, $tubeName)
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
            PheanstalkInterface::DEFAULT_PRIORITY,
            QueueUtils::calculateDelay($eta)
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
