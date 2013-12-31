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
        return new PdoHandler(array(
            'dsn'        => $config['db_pdo_mysql_dsn'],
            'username'   => $config['db_pdo_mysql_username'],
            'password'   => $config['db_pdo_mysql_password'],
            'table_name' => $config['db_pdo_mysql_table_name'],
        ));
    }
}
