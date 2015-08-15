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
 * @requires extension pdo_pgsql
 */
class PgsqlPdoQueueTest extends PdoQueueTest
{
    public static function createHandler(array $config)
    {
        return new PdoHandler([
            'dsn'        => $config['PHIVE_PDO_PGSQL_DSN'],
            'username'   => $config['PHIVE_PDO_PGSQL_USERNAME'],
            'password'   => $config['PHIVE_PDO_PGSQL_PASSWORD'],
            'table_name' => $config['PHIVE_PDO_PGSQL_TABLE_NAME'],
        ]);
    }
}
