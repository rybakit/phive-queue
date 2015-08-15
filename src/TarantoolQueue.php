<?php

/*
 * This file is part of the Phive Queue package.
 *
 * (c) Eugene Leonovich <gen.work@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
        $item .= '         ';
        $eta = QueueUtils::calculateDelay($eta);

        $this->tarantool->call('queue.put', [
            $this->space,
            $this->tubeName,
            (string) $eta,
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

        if (0 === $result['count']) {
            throw new NoItemAvailableException($this);
        }

        $tuple = $result['tuples_list'][0];

        $this->tarantool->call('queue.delete', [
            $this->space,
            $tuple[0],
        ]);

        return substr($tuple[3], 0, -9);
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

        $tuple = $result['tuples_list'][0];
        $index = array_search("space{$this->space}.{$this->tubeName}.tasks.total", $tuple, true);

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
