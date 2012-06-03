<?php

namespace Phive\Serializer;

class IgbinarySerializer implements SerializerInterface
{
    public function serialize($data)
    {
        return igbinary_serialize($data);
    }

    public function unserialize($data)
    {
        return igbinary_unserialize($data);
    }
}
