<?php

namespace Phive\Tests\Queue\Db\Pdo;

class MysqlQueueTest extends AbstractQueueTestCase
{
    protected static function getDriverName()
    {
        return 'mysql';
    }
}
