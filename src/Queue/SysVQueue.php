<?php

namespace Phive\Queue\Queue;

use Phive\Queue\Exception\NoItemException;
use Phive\Queue\Exception\RuntimeException;
use Phive\Queue\QueueUtils;

class SysVQueue implements QueueInterface
{
    const MSG_MAX_SIZE = 512;

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
    private $perms;

    /**
     * @param int       $key
     * @param bool|null $serialize
     * @param int|null  $perms
     */
    public function __construct($key, $serialize = null, $perms = null)
    {
        $this->key = $key;
        $this->serialize = (bool) $serialize;
        $this->perms = (null === $perms) ? 0666 : $perms;
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
        if (msg_receive($this->getQueue(), -time(), $eta, static::MSG_MAX_SIZE, $item, $this->serialize, MSG_IPC_NOWAIT, $errorCode)) {
            return $item;
        }

        if (MSG_ENOMSG === $errorCode) {
            throw new NoItemException();
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
