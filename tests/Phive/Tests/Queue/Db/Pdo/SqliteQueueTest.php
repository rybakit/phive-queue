<?php

namespace Phive\Tests\Queue\Db\Pdo;

use Phive\Queue\Db\Pdo\SqliteQueue;

class SqliteQueueTest extends AbstractQueueTestCase
{
    protected static function getDriverName()
    {
        return 'sqlite';
    }

    protected static function createConnection()
    {
        $dsn = sprintf('sqlite:%s/phive_tests.sq3', sys_get_temp_dir());

        return new \PDO($dsn);
    }
}
