<?php

namespace Phive\Queue\Redis;

class IterableResult extends \ArrayIterator
{
    /**
     * A PHP callback to transform result set into another structure (e.g. object).
     *
     * @var \Closure|string|array
     */
    protected $hydrator;

    /**
     * Constructor.
     *
     * @param array $data
     * @param \Closure|string|array $hydrator
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $data, $hydrator)
    {
        if (!is_callable($hydrator)) {
            throw new \InvalidArgumentException('The given hydrator is not a valid callable.');
        }

        $this->hydrator = $hydrator;

        parent::__construct($data);
    }

    public function current()
    {
        $current = parent::current();

        return call_user_func($this->hydrator, $current);
    }
}