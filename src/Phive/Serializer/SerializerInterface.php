<?php

namespace Phive\Serializer;

interface SerializerInterface
{
    function serialize($data);
    function unserialize($data);
}