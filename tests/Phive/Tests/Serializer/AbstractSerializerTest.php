<?php

namespace Phive\Tests\Serializer;

abstract class AbstractSerializerTest extends \PHPUnit_Framework_TestCase
{
    public function testSerializeAndUnserealize()
    {
        $data = array(1, 'text', new \stdClass());
        $serializer = $this->createSerializer();

        $serializedData = $serializer->serialize($data);
        $this->assertInternalType('string', $serializedData);

        $this->assertEquals($data, $serializer->unserialize($serializedData));
    }

    /*
    public function testUnserializingBadlySerializedString()
    {
        $serializer = $this->createSerializer();

        try {
            $serializer->unserialize('asd');
            $this->fail('unserialize() throws an exception on badly serialized string');
        } catch (\Exception $e) {
            $this->assertInstanceOf('\Exception', $e, 'unserialize() throws an exception on badly serialized string');
        }
    }
    */

    /**
     * @return \Phive\Serializer\SerializerInterface;
     */
    abstract protected function createSerializer();
}
