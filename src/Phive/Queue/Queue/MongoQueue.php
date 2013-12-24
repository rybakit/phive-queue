<?php

namespace Phive\Queue\Queue;

use Phive\Queue\Exception\InvalidArgumentException;
use Phive\Queue\Exception\NoItemException;
use Phive\Queue\Exception\RuntimeException;
use Phive\Queue\QueueUtils;

class MongoQueue implements QueueInterface
{
    /**
     * @var \MongoClient
     */
    private $client;

    /**
     * @var \MongoCollection
     */
    private $coll;

    /**
     * @var array
     */
    private $options;

    /**
     * @param \MongoClient $client
     * @param array        $options
     *
     * @throws InvalidArgumentException
     */
    public function __construct(\MongoClient $client, array $options)
    {
        if (!isset($options['db'], $options['coll'])) {
            throw new InvalidArgumentException(sprintf(
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

        if (isset($result['value']['item'])) {
            return $result['value']['item'];
        }

        throw new NoItemException();
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
