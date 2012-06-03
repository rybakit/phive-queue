<?php

namespace Phive\Tests\Serializer;

use Phive\Serializer\JsonSerializer;

class JsonSerializerTest extends AbstractSerializerTest
{
    protected function createSerializer()
    {
        return new JsonSerializer();
    }
}
