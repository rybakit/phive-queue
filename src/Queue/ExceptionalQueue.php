<?php

namespace Phive\Queue\Queue;

use Phive\Queue\Exception\ExceptionInterface;
use Phive\Queue\Exception\RuntimeException;

class ExceptionalQueue implements QueueInterface
{
    private $queue;

    public function __construct(QueueInterface $queue)
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
        $this->exceptional(function (QueueInterface $queue) use ($item, $eta) {
            $queue->push($item, $eta);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function pop()
    {
        return $this->exceptional(function (QueueInterface $queue) {
            return $queue->pop();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->exceptional(function (QueueInterface $queue) {
            return $queue->count();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->exceptional(function (QueueInterface $queue) {
            $queue->clear();
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
            $result = $func($this->queue);
        } catch (ExceptionInterface $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new RuntimeException($e->getMessage(), 0, $e);
        }

        return $result;
    }
}
