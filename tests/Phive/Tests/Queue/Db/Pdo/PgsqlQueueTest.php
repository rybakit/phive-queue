<?php

namespace Phive\Tests\Queue\Db\Pdo;

use Phive\Queue\Db\Pdo\PgsqlQueue;

class PgsqlQueueTest extends AbstractQueueTestCase
{
    protected static function getDriverName()
    {
        return 'pgsql';
    }

    protected static function createConnection()
    {
        $dsn = sprintf('pgsql:host=%s;port=%d;dbname=%s',
            isset($GLOBALS['db_pg_host']) ? $GLOBALS['db_pg_host'] : 'localhost',
            isset($GLOBALS['db_pg_port']) ? $GLOBALS['db_pg_port'] : '5432',
            isset($GLOBALS['db_pg_db_name']) ? $GLOBALS['db_pg_db_name'] : 'phive_tests'
        );

        $username = isset($GLOBALS['db_pg_username']) ? $GLOBALS['db_pg_username'] : 'postgres';
        $password = isset($GLOBALS['db_pg_password']) ? $GLOBALS['db_pg_password'] : '';

        return new \PDO($dsn, $username, $password);
    }
}
