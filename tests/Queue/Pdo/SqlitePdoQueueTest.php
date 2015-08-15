<?php

/*
 * This file is part of the Phive Queue package.
 *
 * (c) Eugene Leonovich <gen.work@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phive\Queue\Tests\Queue\Pdo;

use Phive\Queue\Tests\Handler\PdoHandler;

/**
 * @requires extension pdo_sqlite
 */
class SqlitePdoQueueTest extends PdoQueueTest
{
    public static function createHandler(array $config)
    {
        // Generate a new db file on every method call to prevent
        // a "Database schema has changed" error which occurs if any
        // other process (e.g. worker) is still using the old db file.
        // We also can't use the shared cache mode due to
        // @link http://stackoverflow.com/questions/9150319/enable-shared-pager-cache-in-sqlite-using-php-pdo

        return new PdoHandler([
            'dsn'        => sprintf('sqlite:%s/%s.sq3', sys_get_temp_dir(), uniqid('phive_tests_')),
            'username'   => null,
            'password'   => null,
            'table_name' => 'queue',
        ]);
    }
}
