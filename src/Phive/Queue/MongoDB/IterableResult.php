<?php

namespace Phive\Queue\MongoDB;

class IterableResult implements \Iterator
{
    /**
     * The PHP MongoCursor instance.
     *
     * @var \MongoCursor
     */
    protected $cursor;

    /**
     * A PHP callback to transform result set into another structure (e.g. object).
     *
     * @var \Closure|string|array
     */
    protected $hydrator;

    /**
     * Constructor.
     *
     * @param \MongoCursor $cursor
     * @param \Closure|string|array $hydrator
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(\MongoCursor $cursor, $hydrator)
    {
        if (!is_callable($hydrator)) {
            throw new \InvalidArgumentException('The given hydrator is not a valid callable.');
        }

        $this->cursor = $cursor;
        $this->hydrator = $hydrator;
    }

    public function current()
    {
        $data = $this->cursor->current();

        return $data ? call_user_func($this->hydrator, $data) : false;
    }

    public function key()
    {
        return $this->cursor->key();
    }

    public function rewind()
    {
        return $this->cursor->rewind();
    }

    public function next()
    {
        return $this->cursor->next();
    }

    public function valid()
    {
        return $this->cursor->valid();
    }
}