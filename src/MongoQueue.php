<?php

/*
 * This file is part of the Phive Queue package.
 *
 * (c) Eugene Leonovich <gen.work@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phive\Queue;

class MongoQueue implements Queue
{
    /**
     * @var \MongoClient
     */
    private $mongoClient;

    /**
     * @var string
     */
    private $dbName;

    /**
     * @var string
     */
    private $collName;

    /**
     * @var \MongoCollection
     */
    private $coll;

    public function __construct(\MongoClient $mongoClient, $dbName, $collName)
    {
        $this->mongoClient = $mongoClient;
        $this->dbName = $dbName;
        $this->collName = $collName;
    }

    /**
     * {@inheritdoc}
     */
    public function push($item, $eta = null)
    {
        $doc = [
            'eta'  => QueueUtils::normalizeEta($eta),
            'item' => $item,
        ];

        $this->getCollection()->insert($doc);
    }

    /**
     * {@inheritdoc}
     */
    public function pop()
    {
        $result = $this->getCollection()->findAndModify(
             ['eta' => ['$lte' => time()]],
             [],
             ['item' => 1, '_id' => 0],
             ['remove' => 1, 'sort' => ['eta' => 1]]
        );

        if ($result && array_key_exists('item', $result)) {
            return $result['item'];
        }

        throw new NoItemAvailableException($this);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->getCollection()->count();
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->getCollection()->remove();
    }

    protected function getCollection()
    {
        if (!$this->coll) {
            $this->coll = $this->mongoClient->selectCollection(
                $this->dbName,
                $this->collName
            );
        }

        return $this->coll;
    }
}
