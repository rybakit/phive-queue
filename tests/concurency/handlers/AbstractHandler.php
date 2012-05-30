<?php

abstract class AbstractHandler
{
    protected $namespace;
    protected $size = 100;

    private static $queue;

    public function __construct($namespace, $size = null)
    {
        $this->namespace = $namespace;

        if (null !== $size) {
            $this->size = $size;
        }

        $this->setup();
    }

    public function handle($action)
    {
        if (method_exists($this, $action)) {
            return $this->$action();
        }

        throw new \InvalidArgumentException(sprintf('Undefined action "%s".', $action));
    }

    public function push()
    {
        $i = 0;
        while ($i < $this->size) {
            $i++;
            $item = "$this->namespace:$i";
            $this->getQueue()->push($item);
            echo "$item\n";
        }

        echo "$this->namespace: $i items were successfully pushed to the queue.\n";
    }

    public function pop()
    {
        $i = 0;
        while ($item = $this->getQueue()->pop()) {
            echo "$this->namespace: $item\n";
            $i++;
        }

        echo "$this->namespace: $i items were successfully popped from the queue.\n";
    }

    public function status()
    {
        $count = $this->getQueue()->count();

        echo "queue size: $count\n";
    }

    public function prepare()
    {
    }

    public function shutdown()
    {
    }

    protected function setup()
    {
    }

    /**
     * @return \Phive\Queue\AdvancedQueueInterface
     */
    protected function getQueue()
    {
        if (!self::$queue) {
            self::$queue = $this->createQueue();
        }

        return self::$queue;
    }

    abstract protected function createQueue();
}
