<?php

namespace Phive\Tests\Serializer;

use Phive\Serializer\PhpSerializer;

class PhpSerializerTest extends AbstractSerializerTest
{
    protected function createSerializer()
    {
        return new PhpSerializer();
    }
}
