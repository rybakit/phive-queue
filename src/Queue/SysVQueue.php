<?php

namespace Phive\Queue\Queue;

use Phive\Queue\Exception\NoItemAvailableException;
use Phive\Queue\Exception\RuntimeException;
use Phive\Queue\QueueUtils;

class SysVQueue implements QueueInterface
{
    const DEFAULT_MSG_MAX_SIZE = 512;
    const DEFAULT_PERMS = 0666;

    /**
     * @var int
     */
    private $key;

    /**
     * @var resource
     */
    private $queue;

    /**
     * @var bool
     */
    private $serialize;

    /**
     * @var int
     */
    private $msgMaxSize;

    /**
     * @var int
     */
    private $perms;

    public function __construct($key, $serialize = null, $msgMaxSize = null, $perms = null)
    {
        $this->key = $key;
        $this->serialize = (bool) $serialize;
        $this->msgMaxSize = (null === $msgMaxSize) ? static::DEFAULT_MSG_MAX_SIZE : $msgMaxSize;
        $this->perms = (null === $perms) ? static::DEFAULT_PERMS : $perms;
    }

    /**
     * {@inheritdoc}
     */
    public function push($item, $eta = null)
    {
        $eta = QueueUtils::normalizeEta($eta);
        msg_send($this->getQueue(), $eta, $item, $this->serialize, false);
    }

    /**
     * {@inheritdoc}
     */
    public function pop()
    {
        if (msg_receive($this->getQueue(), -time(), $eta, $this->msgMaxSize, $item, $this->serialize, MSG_IPC_NOWAIT, $errorCode)) {
            return $item;
        }

        if (MSG_ENOMSG === $errorCode) {
            throw new NoItemAvailableException();
        }

        throw new RuntimeException('Pop failed.', $errorCode);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        $stat = msg_stat_queue($this->getQueue());

        return $stat['msg_qnum'];
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        msg_remove_queue($this->getQueue());
    }

    protected function getQueue()
    {
        if (!is_resource($this->queue)) {
            $this->queue = msg_get_queue($this->key, $this->perms);
        }

        return $this->queue;
    }
}
