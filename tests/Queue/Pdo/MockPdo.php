<?php

namespace Phive\Queue\Tests\Queue\Pdo;

class MockPdo extends \PDO
{
    public $errorMode = self::ERRMODE_EXCEPTION;
    public $driverName;

    public function __construct()
    {
    }

    public function getAttribute($attribute)
    {
        switch ($attribute) {
            case self::ATTR_ERRMODE:
                return $this->errorMode;

            case self::ATTR_DRIVER_NAME:
                return $this->driverName;

            default:
                return parent::getAttribute($attribute);
        }
    }
}
