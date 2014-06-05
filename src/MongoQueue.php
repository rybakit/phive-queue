<?php

namespace Phive\Queue;

class MongoQueue implements Queue
{
    /**
     * @var \MongoClient
     */
    private $client;

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

    public function __construct(\MongoClient $client, $dbName, $collName)
    {
        $this->client = $client;
        $this->dbName = $dbName;
        $this->collName = $collName;
    }

    public function getClient()
    {
        return $this->client;
    }

    /**
     * {@inheritdoc}
     */
    public function push($item, $eta = null)
    {
        $this->getCollection()->insert([
            'eta'  => norm_eta($eta),
            'item' => $item,
        ]);
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
            $this->coll = $this->client->selectCollection(
                $this->dbName,
                $this->collName
            );
        }

        return $this->coll;
    }
}
