<?php

namespace Phive\Serializer;

interface SerializerInterface
{
    /**
     * @param mixed $data
     *
     * @return string
     */
    function serialize($data);

    /**
     * @param string $data
     *
     * @return mixed
     */
    function unserialize($data);
}
