<?php

namespace Phive\Queue\MongoDb;

use Phive\Queue\CallbackIterator;
use Phive\Queue\RuntimeException;
use Phive\Queue\QueueInterface;
use Phive\Queue\QueueUtils;

class MongoDbQueue implements QueueInterface
{
    /**
     * @var \MongoClient
     */
    protected $client;

    /**
     * @var \MongoCollection
     */
    protected $coll;

    /**
     * @var array
     */
    protected $options;

    /**
     * @param \MongoClient $client
     * @param array        $options
     */
    public function __construct(\MongoClient $client, array $options)
    {
        if (!isset($options['db'], $options['coll'])) {
            throw new \InvalidArgumentException(sprintf(
                'The "db" and "coll" option are required for %s.', __CLASS__
            ));
        }

        $this->client = $client;
        $this->options = $options;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function getCollection()
    {
        if (!$this->coll) {
            $this->coll = $this->client->selectCollection(
                $this->options['db'],
                $this->options['coll']
            );
        }

        return $this->coll;
    }

    /**
     * {@inheritdoc}
     */
    public function push($item, $eta = null)
    {
        $eta = QueueUtils::normalizeEta($eta);

        $this->exceptional(function (\MongoCollection $coll) use ($eta, $item) {
            $coll->insert(array(
                'eta'  => $eta,
                'item' => $item,
            ));
        });
    }

    /**
     * {@inheritdoc}
     */
    public function pop()
    {
        $result = $this->exceptional(function (\MongoCollection $coll) {
            return $coll->db->command(array(
                'findandmodify' => $coll->getName(),
                'remove'        => 1,
                'fields'        => array('item' => 1),
                'query'         => array('eta' => array('$lte' => time())),
                'sort'          => array('eta' => 1),
            ));
        });

        return isset($result['value']['item'])
            ? $result['value']['item']
            : false;
    }

    /**
     * {@inheritdoc}
     */
    public function slice($offset, $limit)
    {
        $cursor = $this->exceptional(function (\MongoCollection $coll) {
            return $coll->find(array('eta' => array('$lte' => time())));
        });

        $cursor
            ->sort(array('eta' => 1))
            ->skip(QueueUtils::normalizeOffset($offset))
            ->limit(QueueUtils::normalizeLimit($limit))
        ;

        return new CallbackIterator($cursor, function ($data) {
            return $data['item'];
        });
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->exceptional(function (\MongoCollection $coll) {
            return $coll->count();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->exceptional(function (\MongoCollection $coll) {
            $coll->remove();
        });
    }

    /**
     * @param \Closure $func The function to execute.
     *
     * @return mixed
     *
     * @throws RuntimeException
     */
    protected function exceptional(\Closure $func)
    {
        try {
            $result = $func($this->getCollection());
        } catch (\MongoException $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        return $result;
    }
}
