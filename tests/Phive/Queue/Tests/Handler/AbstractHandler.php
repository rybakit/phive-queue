<?php

namespace Phive\Queue\Tests\Handler;

abstract class AbstractHandler implements \Serializable
{
    /**
     * @var array
     */
    protected $options;

    public function __construct(array $options = array())
    {
        $this->options = $options;
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
     * @return \Phive\Queue\Queue\QueueInterface
     */
    abstract public function createQueue();
}
