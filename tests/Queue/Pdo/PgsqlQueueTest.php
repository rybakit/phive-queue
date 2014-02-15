<?php

namespace Phive\Queue\Tests\Queue\Pdo;

use Phive\Queue\Tests\Handler\PdoHandler;

/**
 * @requires extension pdo_pgsql
 */
class PgsqlQueueTest extends AbstractPdoQueueTest
{
    public static function createHandler(array $config)
    {
        return new PdoHandler([
            'dsn'        => $config['pdo_pgsql_dsn'],
            'username'   => $config['pdo_pgsql_username'],
            'password'   => $config['pdo_pgsql_password'],
            'table_name' => $config['pdo_pgsql_table_name'],
        ]);
    }
}
