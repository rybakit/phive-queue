<?php

namespace Phive\Tests\Queue;

abstract class AbstractQueueManager
{
    /**
     * @var array
     */
    protected $options = array();

    public function __construct(array $options = array())
    {
        $this->options = $options;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param string $name
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public function getOption($name)
    {
        if (array_key_exists($name, $this->options)) {
            return $this->options[$name];
        }

        throw new \InvalidArgumentException(sprintf('Option "%s" is not found.', $name));
    }

    /**
     * @return \Phive\Queue\QueueInterface
     */
    abstract public function createQueue();

    abstract public function reset();
}
