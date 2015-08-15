<?php

/*
 * This file is part of the Phive Queue package.
 *
 * (c) Eugene Leonovich <gen.work@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phive\Queue\Tests\Queue;

use Phive\Queue\Queue;

trait Util
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

    public function provideItemsOfVariousTypes()
    {
        $data = [];

        foreach (Types::getAll() as $type => $item) {
            $data[$type] = [$item, $type];
        }

        return $data;
    }

    public function provideItemsOfSupportedTypes()
    {
        return array_diff_key(
            $this->provideItemsOfVariousTypes(),
            array_fill_keys($this->getUnsupportedItemTypes(), false)
        );
    }

    public function provideItemsOfUnsupportedTypes()
    {
        return array_intersect_key(
            $this->provideItemsOfVariousTypes(),
            array_fill_keys($this->getUnsupportedItemTypes(), false)
        );
    }

    protected function getUnsupportedItemTypes()
    {
        return [];
    }
}
