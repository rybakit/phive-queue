<?php

namespace Phive\Queue;

use Predis\Client;

class PredisQueue implements Queue
{
    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function push($item, $eta = null)
    {
        $this->client->zadd('items', norm_eta($eta), $item);
    }

    /**
     * {@inheritdoc}
     */
    public function pop()
    {
        $result = $this->atomicPop('items');

        if ($result === null) {
            throw new NoItemAvailableException($this);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->client->del('items');
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->client->zcard('items');
    }

    /**
     * Implements an atomic, client-side POP operation using check-and-set operations 
     * with MULTI/EXEC transactions.
     * 
     * @param string $key
     *
     * @return string
     */
    private function atomicPop($key)
    {
        $element = null;
        $options = ['cas' => true, 'watch' => $key, 'retry' => 100];

        $this->client->transaction($options, function ($tx) use ($key, &$element) {
            $params = ['withscores' => true, 'limit' => ['offset' => 0, 'count' => 1]];

            @list($element) = $tx->zrangebyscore($key, '-inf', time(), $params);

            if (isset($element) && is_array($element)) {
                $tx->multi();
                $tx->zrem($key, $element[0]);
            }
        });

        return $element ? $element[0] : null;
    }
}
