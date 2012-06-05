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

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->innerQueue = new \SplPriorityQueue();
    }

    /**
     * @see \Phive\Queue\QueueInterface::push()
     */
    public function push($item, $eta = null)
    {
        $eta = $eta ? $this->normalizeEta($eta) : time();
        $this->innerQueue->insert($item, array(-$eta, $this->queueOrder--));
    }

    /**
     * @see \Phive\Queue\QueueInterface::pop()
     */
    public function pop()
    {
        if (!$this->innerQueue->isEmpty()) {
            $this->innerQueue->setExtractFlags(\SplPriorityQueue::EXTR_PRIORITY);
            list($eta,) = $this->innerQueue->top();
            $this->innerQueue->setExtractFlags(\SplPriorityQueue::EXTR_DATA);

            if (time() + $eta >= 0) {
                return $this->innerQueue->extract();
            }
        }

        return false;
    }

    /**
     * @see \Phive\Queue\QueueInterface::peek()
     */
    public function peek($limit = 1, $skip = 0)
    {
        $this->assertLimit($limit, $skip);

        return new \LimitIterator(clone $this->innerQueue, $skip, $limit);
    }

    /**
     * @see \Phive\Queue\QueueInterface::count()
     */
    public function count()
    {
        return $this->innerQueue->count();
    }

    /**
     * @see \Phive\Queue\QueueInterface::clear()
     */
    public function clear()
    {
        $this->innerQueue = new \SplPriorityQueue();
        $this->queueOrder = PHP_INT_MAX;
    }
}
