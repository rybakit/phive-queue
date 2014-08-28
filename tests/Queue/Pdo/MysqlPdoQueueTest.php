<?php

namespace Phive\Queue\Tests\Queue\Pdo;

use Phive\Queue\Tests\Handler\PdoHandler;
use Phive\Queue\Tests\Queue\Types;

/**
 * @requires extension pdo_mysql
 */
class MysqlPdoQueueTest extends PdoQueueTest
{
    public function getUnsupportedItemTypes()
    {
        return [Types::TYPE_ARRAY, Types::TYPE_OBJECT];
    }

    public static function createHandler(array $config)
    {
        return new PdoHandler([
            'dsn'        => $config['PHIVE_PDO_MYSQL_DSN'],
            'username'   => $config['PHIVE_PDO_MYSQL_USERNAME'],
            'password'   => $config['PHIVE_PDO_MYSQL_PASSWORD'],
            'table_name' => $config['PHIVE_PDO_MYSQL_TABLE_NAME'],
        ]);
    }
}
