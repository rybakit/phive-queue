<?php

namespace Phive\Queue\MongoDb;

use Phive\CallbackIterator;
use Phive\Exception\RuntimeException;
use Phive\Queue\AbstractQueue;

class MongoDbQueue extends AbstractQueue
{
    /**
     * @var \MongoCollection
     */
    protected $collection;

    /**
     * Constructor.
     *
     * @param \MongoCollection $collection
     */
    public function __construct(\MongoCollection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * Retrieves \MongoCollection instance.
     *
     * @return \MongoCollection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @see \Phive\Queue\QueueInterface::push()
     */
    public function push($item, $eta = null)
    {
        $eta = $eta ? $this->normalizeEta($eta) : time();

        $data = array(
            'eta'  => $eta,
            'item' => $item,
        );

        $result = $this->collection->insert($data, array('safe' => true));
        if (!$result['ok']) {
            throw new RuntimeException($result['errmsg']);
        }
    }

    /**
     * @see \Phive\Queue\QueueInterface::pop()
     */
    public function pop()
    {
        $command = array(
            'findandmodify' => $this->collection->getName(),
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
     * @see \Phive\Queue\QueueInterface::peek()
     */
    public function peek($limit = 1, $skip = 0)
    {
        $this->assertLimit($limit, $skip);

        $cursor = $this->collection->find(array('eta' => array('$lte' => time())));
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
     * @see \Phive\Queue\QueueInterface::count()
     */
    public function count()
    {
        return $this->collection->count();
    }

    /**
     * @see \Phive\Queue\QueueInterface::clear()
     */
    public function clear()
    {
        $this->collection->remove(array(), array('safe' => true));
    }
}
