<?php

namespace Phive\Queue;

class TarantoolQueue implements Queue
{
    /**
     * @var \Tarantool
     */
    private $tarantool;

    /**
     * @var int
     */
    private $space;

    /**
     * @var string
     */
    private $tubeName;

    public function __construct(\Tarantool $tarantool, $tubeName, $space = null)
    {
        $this->tarantool = $tarantool;
        $this->space = null === $space ? 0 : $space;
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
        $result = $this->tarantool->call('queue.statistics', []);
        if (empty($result['count'])) {
            return 0;
        }

        $tuples = $result['tuples_list'];
        $index = array_search("space{$this->space}.{$this->tubeName}.tasks.total", $tuples[0], true);

        if (false !== $index) {
            return (int) $tuples[0][$index + 1];
        }

        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->tarantool->call('queue.truncate', [$this->space, $this->tubeName]);
    }
}
