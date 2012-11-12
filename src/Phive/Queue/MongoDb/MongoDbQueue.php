<?php

namespace Phive\Queue\MongoDb;

use Phive\CallbackIterator;
use Phive\RuntimeException;
use Phive\Queue\AbstractQueue;

class MongoDbQueue extends AbstractQueue
{
    /**
     * @var \Mongo
     */
    protected $conn;

    /**
     * @var \MongoCollection
     */
    protected $collection;

    /**
     * @var array
     */
    protected $options;

    /**
     * Constructor.
     *
     * @param \Mongo $conn
     * @param array  $options
     */
    public function __construct(\Mongo $conn, array $options)
    {
        if (!isset($options['database'], $options['collection'])) {
            throw new \InvalidArgumentException(sprintf(
                'The "database" and "collection" option are required for %s.', __CLASS__
            ));
        }

        $this->conn = $conn;
        $this->options = $options;
    }

    /**
     * @return \Mongo
     */
    public function getConnection()
    {
        return $this->conn;
    }

    /**
     * @return \MongoCollection
     */
    public function getCollection()
    {
        if (!$this->collection) {
            $this->collection = $this->conn->selectCollection(
                $this->options['database'],
                $this->options['collection']
            );
        }

        return $this->collection;
    }

    /**
     * {@inheritdoc}
     */
    public function push($item, $eta = null)
    {
        $eta = $eta ? $this->normalizeEta($eta) : time();

        $data = array(
            'eta'  => $eta,
            'item' => $item,
        );

        $result = $this->getCollection()->insert($data, array('safe' => true));
        if (!$result['ok']) {
            throw new RuntimeException($result['errmsg']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function pop()
    {
        $command = array(
            'findandmodify' => $this->getCollection()->getName(),
            'remove'        => 1,
            'fields'        => array('item' => 1),
            'query'         => array('eta' => array('$lte' => time())),
            'sort'          => array('eta' => 1),
        );

        $result = $this->collection->db->command($command);
        if (!$result['ok']) {
            throw new RuntimeException($result['errmsg']);
        }

        $data = $result['value'];

        return $data ? $data['item'] : false;
    }

    /**
     * {@inheritdoc}
     */
    public function peek($limit = 1, $skip = 0)
    {
        $this->assertLimit($limit, $skip);

        $cursor = $this->getCollection()->find(array('eta' => array('$lte' => time())));
        $cursor->sort(array('eta' => 1));

        if ($limit > 0) {
            $cursor->limit($limit);
        }

        if ($skip) {
            $cursor->skip($skip);
        }

        return new CallbackIterator($cursor, function ($data) {
            return $data['item'];
        });
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
        $this->getCollection()->remove(array(), array('safe' => true));
    }
}
