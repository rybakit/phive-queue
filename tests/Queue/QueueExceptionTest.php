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

use Phive\Queue\QueueException;

class QueueExceptionTest extends \PHPUnit_Framework_TestCase
{
    use Util;

    /**
     * @var \Phive\Queue\Queue
     */
    protected $queue;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->queue = $this->getQueueMock();
    }

    public function testQueueExceptionExtendsBaseException()
    {
        $this->assertInstanceOf('Exception', new QueueException($this->queue));
    }

    public function testGetQueue()
    {
        $e = new QueueException($this->queue);

        $this->assertEquals($this->queue, $e->getQueue());
    }

    public function testGetMessage()
    {
        $message = 'Error message';
        $e = new QueueException($this->queue, $message);

        $this->assertEquals($message, $e->getMessage());
    }
}
