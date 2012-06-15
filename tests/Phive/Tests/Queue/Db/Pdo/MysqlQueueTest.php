<?php

namespace Phive\Tests\Queue\Db\Pdo;

class MysqlQueueTest extends AbstractQueueTestCase
{
    protected static function getDriverName()
    {
        return 'mysql';
    }

    protected static function createConnection()
    {
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s',
            isset($GLOBALS['db_my_host']) ? $GLOBALS['db_my_host'] : 'localhost',
            isset($GLOBALS['db_my_port']) ? $GLOBALS['db_my_port'] : '3306',
            isset($GLOBALS['db_my_db_name']) ? $GLOBALS['db_my_db_name'] : 'phive_tests'
        );

        $username = isset($GLOBALS['db_my_username']) ? $GLOBALS['db_my_username'] : 'root';
        $password = isset($GLOBALS['db_my_password']) ? $GLOBALS['db_my_password'] : '';

        return new \PDO($dsn, $username, $password);
    }
}
