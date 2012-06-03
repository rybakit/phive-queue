<?php

namespace Phive\Tests\Serializer;

use Phive\Serializer\IgbinarySerializer;

class IgbinarySerializerTest extends AbstractSerializerTest
{
    public function setUp()
    {
        if (!extension_loaded('igbinary')) {
            $this->markTestSkipped('IgbinarySerializer requires the php "igbinary" extension.');
        }

        parent::setUp();
    }

    protected function createSerializer()
    {
        return new IgbinarySerializer();
    }
}
