<?php

namespace Phive\Queue\Queue;

use Phive\Queue\Exception\NoItemException;
use Phive\Queue\Exception\RuntimeException;
use Phive\Queue\QueueUtils;

class SysVQueue implements QueueInterface
{
    const MSG_MAX_SIZE = 512;

    private $key;

    private $queue;

    private $serialize;

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
        $this->perms = $perms ?: 0666;
    }

    /**
     * {@inheritdoc}
     */
    public function push($item, $eta = null)
    {
        $eta = QueueUtils::normalizeEta($eta);

        $this->exceptional(function () use ($eta, $item) {
            msg_send($this->getQueue(), $eta, $item, $this->serialize, false);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function pop()
    {
        return $this->exceptional(function () {
            if (msg_receive($this->getQueue(), -time(), $eta, static::MSG_MAX_SIZE, $item, $this->serialize, MSG_IPC_NOWAIT, $errorCode)) {
                return $item;
            }

            if (MSG_ENOMSG === $errorCode) {
                throw new NoItemException();
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        $stat = $this->exceptional(function () {
            return msg_stat_queue($this->getQueue());
        });

        return $stat['msg_qnum'];
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->exceptional(function () {
            msg_remove_queue($this->getQueue());
        });
    }

    protected function getQueue()
    {
        if (!is_resource($this->queue)) {
            $this->queue = msg_get_queue($this->key, $this->perms);
        }

        return $this->queue;
    }

    protected function exceptional(\Closure $func)
    {
        set_error_handler(function ($error, $message = '') { throw new RuntimeException($message, $error); }, E_WARNING);
        $result = $func();
        restore_error_handler();

        return $result;
    }
}
