<?php

namespace Phive\Queue;

class InMemoryQueue extends AbstractQueue
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
        $eta = $eta ? $this->normalizeEta($eta) : time();
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
    public function peek($limit = 1, $skip = 0)
    {
        $this->assertLimit($limit, $skip);

        return new \LimitIterator(clone $this->innerQueue, $skip, $limit);
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
