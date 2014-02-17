<?php

namespace Phive\Queue\Tests\Queue\Pdo;

use Phive\Queue\Tests\Handler\PdoHandler;
use Phive\Queue\Tests\Queue\AbstractQueueTest;
use Phive\Queue\Tests\Queue\ConcurrencyTrait;
use Phive\Queue\Tests\Queue\PerformanceTrait;

/**
 * @requires extension pdo_pgsql
 */
class PgsqlQueueTest extends AbstractQueueTest
{
    use PerformanceTrait;
    use ConcurrencyTrait;

    public static function createHandler(array $config)
    {
        return new PdoHandler([
            'dsn'        => $config['PHIVE_PDO_PGSQL_DSN'],
            'username'   => $config['PHIVE_PDO_PGSQL_USERNAME'],
            'password'   => $config['PHIVE_PDO_PGSQL_PASSWORD'],
            'table_name' => $config['PHIVE_PDO_PGSQL_TABLE_NAME'],
        ]);
    }
}
