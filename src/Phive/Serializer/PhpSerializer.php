<?php

namespace Phive\Serializer;

class PhpSerializer implements SerializerInterface
{
    public function serialize($data)
    {
        return serialize($data);
    }

    public function unserialize($data)
    {
        return unserialize($data);
    }
}
