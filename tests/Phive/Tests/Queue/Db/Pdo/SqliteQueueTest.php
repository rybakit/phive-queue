<?php

namespace Phive\Tests\Queue\Db\Pdo;

class SqliteQueueTest extends AbstractPdoQueueTest
{
    public static function createHandler()
    {
        // Generate a new db file on every method call to prevent
        // a "Database schema has changed" error which occurs if any
        // other process (e.g. worker) is still using the old db file.
        // We also can't use the shared cache mode due to
        // @link http://stackoverflow.com/questions/9150319/enable-shared-pager-cache-in-sqlite-using-php-pdo

        return new PdoHandler(array(
            'dsn'        => sprintf('sqlite:%s/%s.sq3', sys_get_temp_dir(), uniqid('phive_tests_')),
            'username'   => null,
            'password'   => null,
            'table_name' => 'queue',
        ));
    }
}
