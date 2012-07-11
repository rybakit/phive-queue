<?php

namespace Phive\Tests\Queue\Db\Pdo;

class SqliteQueueTest extends HandlerAwareQueueTestCase
{
    public static function createHandler()
    {
        return new PdoHandler(array(
            'dsn'           => str_replace('%temp_dir%', sys_get_temp_dir(), $GLOBALS['db_pdo_sqlite_dsn']),
            'username'      => null,
            'password'      => null,
            'table_name'    => $GLOBALS['db_pdo_sqlite_table_name'],
        ));
    }
}
