<?php

namespace Phive\Queue;

use Phive\Serializer\SerializerInterface;
use Phive\Serializer\PhpSerializer;

class SerializerAwareQueue implements QueueInterface
{
    protected $queue;
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
     * @return \Phive\Serializer\PhpSerializer
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
}
