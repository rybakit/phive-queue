<?php

/*
 * This file is part of the Phive Queue package.
 *
 * (c) Eugene Leonovich <gen.work@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phive\Queue\Tests\Handler;

use Phive\Queue\MongoQueue;

class MongoHandler extends Handler
{
    /**
     * @var \MongoClient
     */
    private $mongoClient;

    /**
     * @var \MongoCollection
     */
    private $coll;

    public function createQueue()
    {
        return new MongoQueue(
            $this->mongoClient,
            $this->getOption('db_name'),
            $this->getOption('coll_name')
        );
    }

    public function reset()
    {
        $this->mongoClient->selectDB($this->getOption('db_name'))->drop();
    }

    public function clear()
    {
        $this->coll->remove();
    }

    protected function configure()
    {
        $this->mongoClient = new \MongoClient($this->getOption('server'));
        $this->coll = $this->mongoClient->selectCollection($this->getOption('db_name'), $this->getOption('coll_name'));
        $this->coll->ensureIndex(['eta' => 1]);
    }
}
