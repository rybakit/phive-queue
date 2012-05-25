<?php

class HandlerFactory
{
    protected static $classMap = array(
        'pdo_pgsql' => 'PgSqlPDOHandler',
        'pdo_mysql' => 'MySqlPDOHandler',
        'mongo'     => 'MongoDBHandler',
        'redis'     => 'RedisHandler',
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
