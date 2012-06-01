<?php

class HandlerFactory
{
    protected static $classMap = array(
        'mongo'         => 'MongoDbHandler',
        'redis'         => 'RedisHandler',
        'pdo_pgsql'     => 'PgsqlPdoHandler',
        'pdo_mysql'     => 'MysqlPdoHandler',
        'pdo_sqlite'    => 'SqlitePdoHandler',
    );

    public static function create($alias, $namespace = null, $size = null)
    {
        if (!isset(static::$classMap[$alias])) {
            throw new \InvalidArgumentException(sprintf('Unknown handler "%s".', $alias));
        }

        $className = static::$classMap[$alias];

        return new $className($namespace, $size);
    }
}
