#!/bin/bash

PHP_INI_FILE=$(php -r "echo php_ini_loaded_file();")

## mongo
#wget http://pecl.php.net/get/mongo-1.2.10.tgz > /dev/null 2>&1
#tar -xzf mongo-1.2.10.tgz > /dev/null 2>&1
#sh -c "cd mongo-1.2.10 && phpize && ./configure && make && sudo make install" > /dev/null 2>&1
#echo "extension=mongo.so" >> $PHP_INI_FILE


# redis-server
sudo add-apt-repository -y ppa:rwky/redis-unstable
sudo apt-get update
sudo apt-get install redis-server

# phpredis
wget https://github.com/nicolasff/phpredis/tarball/2.2.1 > /dev/null 2>&1
tar -xzf 2.2.1 > /dev/null 2>&1
sh -c "cd nicolasff-phpredis-250e81b && phpize && ./configure && make && sudo make install" > /dev/null 2>&1
echo "extension=redis.so" >> $PHP_INI_FILE
