<?php

namespace Phive\Queue;

class InMemoryQueue implements QueueInterface
{
    /**
     * @var \SplPriorityQueue
     */
    protected $queue;

    /**
     * @var int
     */
    protected $queueOrder;

    public function __construct()
    {
        $this->queue = new \SplPriorityQueue();
        $this->queueOrder = PHP_INT_MAX;
    }

    /**
     * {@inheritdoc}
     */
    public function push($item, $eta = null)
    {
        $eta = QueueUtils::normalizeEta($eta);
        $this->queue->insert($item, array(-$eta, $this->queueOrder--));
    }

    /**
     * {@inheritdoc}
     */
    public function pop()
    {
        if (!$this->queue->isEmpty()) {
            $this->queue->setExtractFlags(\SplPriorityQueue::EXTR_PRIORITY);
            $priority = $this->queue->top();

            if (time() + $priority[0] >= 0) {
                $this->queue->setExtractFlags(\SplPriorityQueue::EXTR_DATA);

                return $this->queue->extract();
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function slice($offset, $limit)
    {
        $offset = QueueUtils::normalizeOffset($offset);
        $limit = QueueUtils::normalizeLimit($limit);

        $now = time();
        $iterator = new CustomLimitIterator(clone $this->queue,
            function (\SplPriorityQueue $queue) use ($now) {
                $queue->setExtractFlags(\SplPriorityQueue::EXTR_PRIORITY);
                $priority = $queue->current();
                $queue->setExtractFlags(\SplPriorityQueue::EXTR_DATA);

                return $now + $priority[0] >= 0;
            }
        );

        return new \LimitIterator($iterator, $offset, $limit);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->queue->count();
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->queue = new \SplPriorityQueue();
        $this->queueOrder = PHP_INT_MAX;
    }
}
