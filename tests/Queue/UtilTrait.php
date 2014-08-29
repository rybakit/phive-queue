<?php

namespace Phive\Queue\Tests\Queue;

use Phive\Queue\Queue;

trait UtilTrait
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getQueueMock()
    {
        return $this->getMock('Phive\Queue\Queue');
    }

    public function provideQueueInterfaceMethods()
    {
        return array_chunk(get_class_methods('Phive\Queue\Queue'), 1);
    }

    public function callQueueMethod(Queue $queue, $method)
    {
        $r = new \ReflectionMethod($queue, $method);

        if ($num = $r->getNumberOfRequiredParameters()) {
            return call_user_func_array([$queue, $method], array_fill(0, $num, 'foo'));
        }

        return $queue->$method();
    }
}
