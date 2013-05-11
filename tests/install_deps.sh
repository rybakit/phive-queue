#!/bin/sh

PHP_INI_FILE=$(php -r "echo php_ini_loaded_file();")

# redis-server v2.6
sudo add-apt-repository -y ppa:rwky/redis-unstable > /dev/null 2>&1
sudo apt-get update > /dev/null 2>&1
sudo apt-get install redis-server > /dev/null 2>&1

# phpredis
pecl -q install redis
echo "extension=redis.so" >> $PHP_INI_FILE

# mongo
if [ -z "$(php -m | grep mongo)" ]; then
    echo "extension=mongo.so" >> $PHP_INI_FILE
fi
