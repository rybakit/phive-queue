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

    /**
     * {@inheritdoc}
     */
    public function push($item, $eta = null)
    {
        // see https://github.com/tarantool/tarantool/issues/336
        $item = pack('a9', $item);

        $this->tarantool->call('queue.put', [
            $this->space,
            $this->tubeName,
            (string) calc_delay($eta),
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
            '0.00000001',
        ]);

        if (empty($result['count'])) {
            throw new NoItemAvailableException($this);
        }

        $item = $result['tuples_list'][0][3];
        $item = rtrim($item, "\0");

        return $item;
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
