#!/bin/sh

PHP_INI_FILE=$(php -r "echo php_ini_loaded_file();")

# redis-server v2.6
sudo add-apt-repository -y ppa:rwky/redis-unstable > /dev/null 2>&1
sudo apt-get update > /dev/null 2>&1
sudo apt-get install redis-server > /dev/null 2>&1

# phpredis
wget -O phpredis-master.zip https://github.com/nicolasff/phpredis/archive/master.zip > /dev/null 2>&1
unzip phpredis-master.zip > /dev/null 2>&1
sh -c "cd phpredis-master && phpize && ./configure && make && sudo make install" > /dev/null 2>&1
echo "extension=redis.so" >> $PHP_INI_FILE

# mongo
pecl -q install mongo
if [ -z "$(php -m | grep mongo)" ]; then
    echo "extension=mongo.so" >> $PHP_INI_FILE
fi
