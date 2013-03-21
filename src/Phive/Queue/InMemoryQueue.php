<?php

namespace Phive\Queue;

class InMemoryQueue implements QueueInterface
{
    /**
     * @var \SplPriorityQueue
     */
    protected $innerQueue;

    /**
     * @var int
     */
    protected $queueOrder = PHP_INT_MAX;

    public function __construct()
    {
        $this->innerQueue = new \SplPriorityQueue();
    }

    /**
     * {@inheritdoc}
     */
    public function push($item, $eta = null)
    {
        $eta = QueueUtils::normalizeEta($eta);
        $this->innerQueue->insert($item, array(-$eta, $this->queueOrder--));
    }

    /**
     * {@inheritdoc}
     */
    public function pop()
    {
        if (!$this->innerQueue->isEmpty()) {
            $this->innerQueue->setExtractFlags(\SplPriorityQueue::EXTR_PRIORITY);
            list($eta,) = $this->innerQueue->top();

            if (time() + $eta >= 0) {
                $this->innerQueue->setExtractFlags(\SplPriorityQueue::EXTR_DATA);

                return $this->innerQueue->extract();
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

        return new \LimitIterator(clone $this->innerQueue, $offset, $limit);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->innerQueue->count();
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->innerQueue = new \SplPriorityQueue();
        $this->queueOrder = PHP_INT_MAX;
    }
}
