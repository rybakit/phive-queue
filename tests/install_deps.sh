#!/bin/bash

PHP_INI_FILE=$(php -r "echo php_ini_loaded_file();")

# mongo
wget http://pecl.php.net/get/mongo-1.2.7.tgz > /dev/null 2>&1
tar -xzf mongo-1.2.7.tgz > /dev/null 2>&1
sh -c "cd mongo-1.2.7 && phpize && ./configure && make && sudo make install" > /dev/null 2>&1
echo "extension=mongo.so" >> $PHP_INI_FILE


# redis-server
redis-server -v
sudo service redis-server stop

wget http://redis.googlecode.com/files/redis-2.6.0-rc5.tar.gz > /dev/null 2>&1
tar -xzf redis-2.6.0-rc5.tar.gz > /dev/null 2>&1
#sh -c "cd redis-2.6.0-rc5 && make && ./src/redis-server" > /dev/null 2>&1
cd redis-2.6.0-rc5 && make && ./src/redis-server

# phpredis
wget https://github.com/nicolasff/phpredis/tarball/2.2.1 > /dev/null 2>&1
tar -xzf 2.2.1 > /dev/null 2>&1
sh -c "cd nicolasff-phpredis-250e81b && phpize && ./configure && make && sudo make install" > /dev/null 2>&1
echo "extension=redis.so" >> $PHP_INI_FILE
