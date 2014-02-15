<?php

namespace Phive\Queue\Tests\Queue\Pdo;

use Phive\Queue\Tests\Handler\PdoHandler;

/**
 * @requires extension pdo_mysql
 */
class MysqlQueueTest extends AbstractPdoQueueTest
{
    public static function createHandler(array $config)
    {
        return new PdoHandler([
            'dsn'        => $config['pdo_mysql_dsn'],
            'username'   => $config['pdo_mysql_username'],
            'password'   => $config['pdo_mysql_password'],
            'table_name' => $config['pdo_mysql_table_name'],
        ]);
    }
}
