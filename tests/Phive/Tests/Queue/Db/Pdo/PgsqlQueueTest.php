<?php

namespace Phive\Tests\Queue\Db\Pdo;

class PgsqlQueueTest extends AbstractQueueTestCase
{
    protected static function getDriverName()
    {
        return 'pgsql';
    }
}
