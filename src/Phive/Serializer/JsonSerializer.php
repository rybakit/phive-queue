<?php

namespace Phive\Serializer;

class JsonSerializer implements SerializerInterface
{
    public function serialize($data)
    {
        return json_encode($data);
    }

    public function unserialize($data)
    {
        return json_decode($data);
    }
}
