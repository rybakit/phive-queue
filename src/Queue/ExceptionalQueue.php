<?php

namespace Phive\Queue\Queue;

use Phive\Queue\Exception\QueueException;
use Phive\Queue\Exception\RuntimeException;

class ExceptionalQueue implements Queue
{
    private $queue;

    public function __construct(Queue $queue)
    {
        $this->queue = $queue;
    }

    public function getInnerQueue()
    {
        return $this->queue;
    }

    /**
     * {@inheritdoc}
     */
    public function push($item, $eta = null)
    {
        $this->exceptional(function () use ($item, $eta) {
            $this->queue->push($item, $eta);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function pop()
    {
        return $this->exceptional(function () {
            return $this->queue->pop();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->exceptional(function () {
            return $this->queue->count();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->exceptional(function () {
            $this->queue->clear();
        });
    }

    /**
     * @param \Closure $func The function to execute.
     *
     * @return mixed
     *
     * @throws \Exception
     */
    protected function exceptional(\Closure $func)
    {
        try {
            $result = $func();
        } catch (QueueException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new RuntimeException($e->getMessage(), 0, $e);
        }

        return $result;
    }
}
