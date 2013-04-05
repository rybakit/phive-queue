<?php

namespace Phive\Queue;

class CustomLimitIterator extends \IteratorIterator
{
    /**
     * @var \Closure
     */
    protected $closure;

    /**
     * @param \Iterator $iterator
     * @param \Closure  $closure
     */
    public function __construct(\Iterator $iterator, \Closure $closure)
    {
        parent::__construct($iterator);

        $this->closure = $closure;
    }

    public function valid()
    {
        return call_user_func($this->closure, $this->getInnerIterator());
    }
}
