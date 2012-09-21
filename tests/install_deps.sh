#!/bin/sh

PHP_INI_FILE=$(php -r "echo php_ini_loaded_file();")

# redis-server v2.6
sudo add-apt-repository -y ppa:rwky/redis-unstable > /dev/null 2>&1
sudo apt-get update > /dev/null 2>&1
sudo apt-get install redis-server > /dev/null 2>&1

# phpredis
wget https://github.com/nicolasff/phpredis/tarball/22f455593a9027cf935d37d70e198de225283d2e > /dev/null 2>&1
tar -xzf 22f455593a9027cf935d37d70e198de225283d2e > /dev/null 2>&1
sh -c "cd nicolasff-phpredis-22f4555 && phpize && ./configure && make && sudo make install" > /dev/null 2>&1
echo "extension=redis.so" >> $PHP_INI_FILE

# mongo
pecl install mongo
if [ -z "$(php -m | grep mongo)" ]; then
    echo "extension=mongo.so" >> $PHP_INI_FILE
fi

php -m
