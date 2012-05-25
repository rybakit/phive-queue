<?php

namespace Phive\Serializer;

class PhpSerializer implements SerializerInterface
{
    public function serialize($data)
    {
        return base64_encode(serialize($data));
    }

    public function unserialize($data)
    {
        return unserialize(base64_decode($data));
    }
}
