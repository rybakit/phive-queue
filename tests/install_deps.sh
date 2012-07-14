#!/bin/bash

PHP_INI_FILE=$(php -r "echo php_ini_loaded_file();")

# mongo
wget http://pecl.php.net/get/mongo-1.2.7.tgz > /dev/null 2>&1
tar -xzf mongo-1.2.7.tgz > /dev/null 2>&1
sh -c "cd mongo-1.2.7 && phpize && ./configure && make && sudo make install" > /dev/null 2>&1
echo "extension=mongo.so" >> $PHP_INI_FILE

# redis
wget https://github.com/nicolasff/phpredis/tarball/2.2.1 > /dev/null 2>&1
tar -xzf 2.2.1 > /dev/null 2>&1
sh -c "cd nicolasff-phpredis-250e81b && phpize && ./configure && make && sudo make install" > /dev/null 2>&1
echo "extension=redis.so" >> $PHP_INI_FILE
