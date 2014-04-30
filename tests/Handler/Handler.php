<?php

namespace Phive\Queue\Tests\Handler;

abstract class Handler implements \Serializable
{
    /**
     * @var array
     */
    private $options;

    public function __construct($options = null)
    {
        $this->options = (array) $options;
        $this->configure();
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

    public function serialize()
    {
        return serialize($this->options);
    }

    public function unserialize($data)
    {
        $this->options = unserialize($data);
        $this->configure();
    }

    public function reset()
    {
    }

    public function clear()
    {
    }

    protected function configure()
    {
    }

    /**
     * @return \Phive\Queue\Queue
     */
    abstract public function createQueue();
}