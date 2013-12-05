#!/bin/sh

PHP_INI_FILE=$(php -r "echo php_ini_loaded_file();")

# redis
pecl -q install redis
if [ -z "$(php -m | grep redis)" ]; then
    echo "extension=redis.so" >> $PHP_INI_FILE
fi

# mongo
if [ -z "$(php -m | grep mongo)" ]; then
    echo "extension=mongo.so" >> $PHP_INI_FILE
fi
