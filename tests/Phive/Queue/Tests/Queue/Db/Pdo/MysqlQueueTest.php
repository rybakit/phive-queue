<?php

namespace Phive\Queue\Tests\Queue\Db\Pdo;

use Phive\Queue\Tests\Handler\PdoHandler;

class MysqlQueueTest extends AbstractPdoQueueTest
{
    public static function createHandler()
    {
        return new PdoHandler([
            'dsn'        => $GLOBALS['db_pdo_mysql_dsn'],
            'username'   => $GLOBALS['db_pdo_mysql_username'],
            'password'   => $GLOBALS['db_pdo_mysql_password'],
            'table_name' => $GLOBALS['db_pdo_mysql_table_name'],
        ]);
    }
}
