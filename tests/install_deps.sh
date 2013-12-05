#!/bin/sh

PHP_INI_FILE=$(php -r "echo php_ini_loaded_file();")

# phpredis
pecl -q install redis
echo "extension=redis.so" >> $PHP_INI_FILE

# mongo
if [ -z "$(php -m | grep mongo)" ]; then
    echo "extension=mongo.so" >> $PHP_INI_FILE
fi
