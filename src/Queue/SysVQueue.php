<?php

namespace Phive\Queue\Queue;

use Phive\Queue\Exception\NoItemAvailableException;
use Phive\Queue\Exception\RuntimeException;
use Phive\Queue\QueueUtils;

class SysVQueue implements QueueInterface
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
    private $itemMaxLength = 8192;

    /**
     * @var int
     */
    private $perms = 0666;

    /**
     * @var resource
     */
    private $queue;

    public function __construct($key, $serialize = null, $itemMaxLength = null, $perms = null)
    {
        $this->key = $key;
        $this->serialize = (bool) $serialize;

        if (null !== $itemMaxLength) {
            $this->itemMaxLength = $itemMaxLength;
        }
        if (null !== $perms) {
            $this->perms = $perms;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function push($item, $eta = null)
    {
        $eta = QueueUtils::normalizeEta($eta);

        set_error_handler(function() { return true; }, E_WARNING);
        $result = msg_send($this->getQueue(), $eta, $item, $this->serialize, false, $errorCode);
        restore_error_handler();

        if (!$result) {
            throw new RuntimeException(posix_strerror($errorCode), $errorCode);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function pop()
    {
        set_error_handler(function() { return true; }, E_WARNING);
        $result = msg_receive($this->getQueue(), -time(), $eta, $this->itemMaxLength, $item, $this->serialize, MSG_IPC_NOWAIT, $errorCode);
        restore_error_handler();

        if ($result) {
            return $item;
        }

        if (MSG_ENOMSG === $errorCode) {
            throw new NoItemAvailableException();
        }

        throw new RuntimeException(posix_strerror($errorCode), $errorCode);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        $data = msg_stat_queue($this->getQueue());

        if (!is_array($data)) {
            throw new RuntimeException('Failed to get the meta data for the queue.');
        }

        return $data['msg_qnum'];
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        if (!msg_remove_queue($this->getQueue())) {
            throw new RuntimeException('Failed to destroy the queue.');
        }

        $this->queue = null;
    }

    protected function getQueue()
    {
        if (!is_resource($this->queue)) {
            $this->queue = msg_get_queue($this->key, $this->perms);

            if (!is_resource($this->queue)) {
                throw new RuntimeException('Failed to create/attach to the queue.');
            }
        }

        return $this->queue;
    }
}
