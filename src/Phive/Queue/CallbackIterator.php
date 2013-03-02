<?php

namespace Phive\Queue;

class CallbackIterator implements \OuterIterator
{
    /**
     * @var \Iterator
     */
    protected $iterator;

    /**
     * A PHP callback to transform result set into another structure (e.g. object).
     *
     * @var \Closure|string|array
     */
    protected $callback;

    /**
     * Constructor.
     *
     * @param \Iterator             $iterator
     * @param \Closure|string|array $callback
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(\Iterator $iterator, $callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('The given callback is not a valid callable.');
        }

        $this->callback = $callback;
        $this->iterator = $iterator;
    }

    public function getInnerIterator()
    {
        return $this->iterator;
    }

    public function current()
    {
        $current = $this->iterator->current();

        return call_user_func($this->callback, $current);
    }

    public function next()
    {
        $this->iterator->next();
    }

    public function key()
    {
        return $this->iterator->key();
    }

    public function valid()
    {
        return $this->iterator->valid();
    }

    public function rewind()
    {
        $this->iterator->rewind();
    }
}
