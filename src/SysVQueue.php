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

        if (!msg_send($this->getQueue(), $eta, $item, $this->serialize, false, $errorCode)) {
            throw new QueueException($this, self::getErrorMessage($errorCode), $errorCode);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function pop()
    {
        if (!msg_receive($this->getQueue(), -time(), $eta, $this->itemMaxLength, $item, $this->serialize, MSG_IPC_NOWAIT, $errorCode)) {
            throw (MSG_ENOMSG === $errorCode)
                ? new NoItemAvailableException($this)
                : new QueueException($this, self::getErrorMessage($errorCode), $errorCode);
        }

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

    private static function getErrorMessage($errorCode)
    {
        if ($errorCode) {
            return posix_strerror($errorCode).'.';
        }

        $error = error_get_last();
        if ($error && 0 === strpos($error['message'], 'msg_')) {
            return preg_replace('/^msg_[^:]+?\:\s/', '', $error['message']);
        }

        return 'Unknown error.';
    }
}
