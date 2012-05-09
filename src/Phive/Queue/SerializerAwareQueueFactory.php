<?php

namespace Phive\Queue;

use Phive\Serializer\SerializerInterface;

class SerializerAwareQueueFactory
{
    public static function create(QueueInterface $queue, SerializerInterface $serializer = null)
    {
        return $queue instanceof AdvancedQueueInterface
            ? new SerializerAwareAdvancedQueue($queue, $serializer)
            : new SerializerAwareQueue($queue, $serializer);
    }
}