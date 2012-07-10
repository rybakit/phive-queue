<?php

namespace Phive\Tests\Queue\Db\Pdo;

class SqliteQueueTest extends AbstractQueueTestCase
{
    protected static function getDriverName()
    {
        return 'sqlite';
    }
}
