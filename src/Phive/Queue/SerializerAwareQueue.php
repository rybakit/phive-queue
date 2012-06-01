<?php

namespace Phive\Queue;

use Phive\CallbackIterator;
use Phive\Serializer\SerializerInterface;
use Phive\Serializer\PhpSerializer;

class SerializerAwareQueue implements QueueInterface
{
    /**
     * @var QueueInterface
     */
    protected $queue;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    public function __construct(QueueInterface $queue, SerializerInterface $serializer = null)
    {
        $this->queue = $queue;
        $this->serializer = $serializer ?: new PhpSerializer();
    }

    /**
     * @return QueueInterface
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * @return SerializerInterface
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * @see QueueInterface::push()
     */
    public function push($item, $eta = null)
    {
        $item = $this->serializer->serialize($item);
        $this->queue->push($item);
    }

    /**
     * @see QueueInterface::pop()
     */
    public function pop()
    {
        if ($result = $this->queue->pop()) {
            $result = $this->serializer->unserialize($result);
        }

        return $result;
    }

    /**
     * @see QueueInterface::peek()
     */
    public function peek($limit = 1, $skip = 0)
    {
        $iterator = $this->queue->peek($limit, $skip);

        return new CallbackIterator($iterator, array($this->serializer, 'unserialize'));
    }

    /**
     * @see QueueInterface::count()
     */
    public function count()
    {
        return $this->queue->count();
    }

    /**
     * @see QueueInterface::clear()
     */
    public function clear()
    {
        $this->queue->clear();
    }
}
