<?php

namespace Phive;

class PhpSerializer
{
    public function serialize($item)
    {
        return base64_encode(serialize($item));
    }

    public function unserialize($serialized)
    {
        return unserialize(base64_decode($serialized));
    }
}