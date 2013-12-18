<?php

namespace Phive\Queue\Queue;

use Phive\Queue\Exception\NoItemException;
use Phive\Queue\QueueUtils;

class SysVQueue implements QueueInterface
{
    const MSG_MAX_SIZE = 512;

    private $key;

    private $queue;

    public function __construct($key)
    {
        $this->key = $key;
    }

    /**
     * {@inheritdoc}
     */
    public function push($item, $eta = null)
    {
        $eta = QueueUtils::normalizeEta($eta);
        msg_send($this->getQueue(), $eta, $item, false);
    }

    /**
     * {@inheritdoc}
     */
    public function pop()
    {
        if ($res = msg_receive($this->getQueue(), -time(), $eta, static::MSG_MAX_SIZE, $item, false, \MSG_IPC_NOWAIT)) {
            return $item;
        }

        throw new NoItemException();
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

    private function getQueue()
    {
        if (!is_resource($this->queue)) {
            $this->queue = msg_get_queue($this->key);
        }

        return $this->queue;
    }
}
