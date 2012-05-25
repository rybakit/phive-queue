<?php

namespace Phive\Queue;

use Phive\CallbackIterator;
use Phive\Serializer\SerializerInterface;

class SerializerAwareAdvancedQueue extends SerializerAwareQueue implements AdvancedQueueInterface
{
    public function __construct(AdvancedQueueInterface $queue, SerializerInterface $serializer = null)
    {
        parent::__construct($queue, $serializer);
    }

    /**
     * @see AdvancedQueueInterface::peek()
     */
    public function peek($limit = 1, $skip = 0)
    {
        $iterator = $this->queue->peek($limit, $skip);

        return new CallbackIterator($iterator, array($this->serializer, 'unserialize'));
    }

    /**
     * @see AdvancedQueueInterface::count()
     */
    public function count()
    {
        return $this->queue->count();
    }

    /**
     * @see AdvancedQueueInterface::clear()
     */
    public function clear()
    {
        $this->queue->clear();
    }
}
