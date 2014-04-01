<?php

namespace Phive\Queue;

class SysVQueue implements Queue
{
    /**
     * @var int
     */
    private $key;

    /**
     * @var bool
     */
    private $serialize;

    /**
     * @var int
     */
    private $perms = 0666;

    /**
     * @var int
     */
    private $itemMaxLength = 8192;

    /**
     * @var resource
     */
    private $queue;

    public function __construct($key, $serialize = null, $perms = null)
    {
        $this->key = $key;
        $this->serialize = (bool) $serialize;

        if (null !== $perms) {
            $this->perms = $perms;
        }
    }

    public function setItemMaxLength($length)
    {
        $this->itemMaxLength = $length;
    }

    /**
     * {@inheritdoc}
     */
    public function push($item, $eta = null)
    {
        $eta = QueueUtils::normalizeEta($eta);

        $this->exceptional(function (&$errorCode) use ($item, $eta) {
            return msg_send($this->getQueue(), $eta, $item, $this->serialize, false, $errorCode);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function pop()
    {
        $item = null;

        $this->exceptional(function (&$errorCode) use (&$item) {
            return msg_receive($this->getQueue(), -time(), $eta, $this->itemMaxLength,
                $item, $this->serialize, MSG_IPC_NOWAIT, $errorCode);
        });

        return $item;
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        $data = msg_stat_queue($this->getQueue());

        if (!is_array($data)) {
            throw new QueueException($this, 'Failed to get the meta data for the queue.');
        }

        return $data['msg_qnum'];
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        if (!msg_remove_queue($this->getQueue())) {
            throw new QueueException($this, 'Failed to destroy the queue.');
        }

        $this->queue = null;
    }

    private function getQueue()
    {
        if (!is_resource($this->queue)) {
            $this->queue = msg_get_queue($this->key, $this->perms);

            if (!is_resource($this->queue)) {
                throw new QueueException($this, 'Failed to create/attach to the queue.');
            }
        }

        return $this->queue;
    }

    private function exceptional(\Closure $func)
    {
        $message = null;
        $code = null;

        set_error_handler(function ($errNo, $errStr) use (&$message) { $message = $errStr; }, E_NOTICE | E_WARNING);
        $result = $func($code);
        restore_error_handler();

        if ($result) {
            return $result;
        }

        if (MSG_ENOMSG === $code) {
            throw new NoItemAvailableException($this);
        }

        if (!$message) {
            $message = $code ? posix_strerror($code) : 'Unknown SysV error';
        }

        throw new QueueException($this, $message);
    }
}
