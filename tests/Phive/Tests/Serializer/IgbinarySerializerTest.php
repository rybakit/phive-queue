<?php

namespace Phive\Tests\Serializer;

use Phive\Serializer\IgbinarySerializer;

class IgbinarySerializerTest extends AbstractSerializerTest
{
    protected function createSerializer()
    {
        return new IgbinarySerializer();
    }
}
