<?php

namespace Phive\Queue\Queue;

use Phive\Queue\Exception\NoItemAvailableException;
use Phive\Queue\QueueUtils;

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
        $eta = QueueUtils::normalizeEta($eta);

        $this->getCollection()->insert([
            'eta'  => $eta,
            'item' => $item,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function pop()
    {
        $coll = $this->getCollection();

        $result = $coll->db->command([
            'findandmodify' => $this->collName,
            'remove'        => 1,
            'fields'        => ['item' => 1],
            'query'         => ['eta' => ['$lte' => time()]],
            'sort'          => ['eta' => 1],
        ]);

        if (isset($result['value']['item'])) {
            return $result['value']['item'];
        }

        throw new NoItemAvailableException();
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
