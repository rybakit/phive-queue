<?php

namespace Phive\Queue;

use Phive\CallbackIterator;
use Phive\Serializer\SerializerInterface;
use Phive\Serializer\PhpSerializer;

class SerializerAwareQueue implements QueueInterface
{
    /**
     * @var \Phive\Queue\QueueInterface
     */
    protected $queue;

    /**
     * @var \Phive\Serializer\SerializerInterface
     */
    protected $serializer;

    public function __construct(QueueInterface $queue, SerializerInterface $serializer = null)
    {
        $this->queue = $queue;
        $this->serializer = $serializer ?: new PhpSerializer();
    }

    /**
     * @return \Phive\Queue\QueueInterface
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * @return \Phive\Serializer\SerializerInterface
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function push($item, $eta = null)
    {
        $item = $this->serializer->serialize($item);
        $this->queue->push($item);
    }

    /**
     * {@inheritdoc}
     */
    public function pop()
    {
        if ($result = $this->queue->pop()) {
            $result = $this->serializer->unserialize($result);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function peek($limit = 1, $skip = 0)
    {
        $iterator = $this->queue->peek($limit, $skip);

        return new CallbackIterator($iterator, array($this->serializer, 'unserialize'));
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
        $this->queue->clear();
    }
}
