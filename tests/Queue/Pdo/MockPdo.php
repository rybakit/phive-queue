<?php

namespace Phive\Queue\Tests\Queue\Pdo;

class MockPdo extends \PDO
{
    public $driverName;

    public function __construct()
    {
    }

    public function getAttribute($attribute)
    {
        if (self::ATTR_DRIVER_NAME === $attribute) {
            return $this->driverName;
        }

        return parent::getAttribute($attribute);
    }
}
