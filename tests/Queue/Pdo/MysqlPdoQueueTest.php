<?php

namespace Phive\Queue\Tests\Queue\Pdo;

use Phive\Queue\Tests\Handler\PdoHandler;
use Phive\Queue\Tests\Queue\ConcurrencyTrait;
use Phive\Queue\Tests\Queue\PerformanceTrait;
use Phive\Queue\Tests\Queue\QueueTest;

/**
 * @requires extension pdo_mysql
 */
class MysqlPdoQueueTest extends QueueTest
{
    use PerformanceTrait;
    use ConcurrencyTrait;

    public function provideItemsOfVariousSupportedTypes()
    {
        return array_diff_key(parent::provideItemsOfVariousSupportedTypes(), [
            'array'     => false,
            'object'    => false,
        ]);
    }

    public static function createHandler(array $config)
    {
        return new PdoHandler([
            'dsn'        => $config['PHIVE_PDO_MYSQL_DSN'],
            'username'   => $config['PHIVE_PDO_MYSQL_USERNAME'],
            'password'   => $config['PHIVE_PDO_MYSQL_PASSWORD'],
            'table_name' => $config['PHIVE_PDO_MYSQL_TABLE_NAME'],
        ]);
    }
}
