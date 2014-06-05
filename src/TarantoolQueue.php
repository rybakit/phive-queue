<?php

namespace Phive\Queue;

class TarantoolQueue implements Queue
{
    /**
     * @var \Tarantool
     */
    private $tarantool;

    /**
     * @var string
     */
    private $space;

    /**
     * @var string
     */
    private $tubeName;

    public function __construct(\Tarantool $tarantool, $tubeName, $space = null)
    {
        $this->tarantool = $tarantool;
        $this->space = null === $space ? '0' : (string) $space;
        $this->tubeName = $tubeName;
    }

    public function getTarantool()
    {
        return $this->tarantool;
    }

    /**
     * {@inheritdoc}
     */
    public function push($item, $eta = null)
    {
        $delay = (null !== $eta) ? normalize_eta($eta) - time() : 0;

        $this->tarantool->call('queue.put', [
            $this->space,
            $this->tubeName,
            "$delay",
            '0',
            '0',
            '0',
            $item,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function pop()
    {
        $result = $this->tarantool->call('queue.take', [
            $this->space,
            $this->tubeName,
            '0.0001',
        ]);

        if (empty($result['count'])) {
            throw new NoItemAvailableException($this);
        }

        return $result['tuples_list'][0][3];
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        $result = $this->tarantool->call('queue.statistics', [
            $this->space,
            $this->tubeName,
        ]);

        if (empty($result['tuples_list'][0])) {
            throw new QueueException($this, 'Failed to count items.');
        }

        $tuple = $result['tuples_list'][0];
        $index = array_search("space{$this->space}.{$this->tubeName}.tasks.total", $tuple, true);

        if (false === $index || !isset($tuple[$index + 1])) {
            throw new QueueException($this, 'Failed to count items.');
        }

        return (int) $tuple[$index + 1];
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->tarantool->call('queue.truncate', [$this->space, $this->tubeName]);
    }
}
