<?php

namespace Phive\Queue\Tests\Queue\Pdo;

use Phive\Queue\Tests\Handler\PdoHandler;

class PgsqlQueueTest extends AbstractPdoQueueTest
{
    public static function createHandler()
    {
        return new PdoHandler(array(
            'dsn'        => $GLOBALS['db_pdo_pgsql_dsn'],
            'username'   => $GLOBALS['db_pdo_pgsql_username'],
            'password'   => $GLOBALS['db_pdo_pgsql_password'],
            'table_name' => $GLOBALS['db_pdo_pgsql_table_name'],
        ));
    }
}
