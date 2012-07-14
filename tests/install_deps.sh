#!/bin/bash

wget http://pecl.php.net/get/mongo-1.2.7.tgz > /dev/null 2>&1
tar -xzf mongo-1.2.7.tgz > /dev/null 2>&1
sh -c "cd mongo-1.2.7 && phpize && ./configure && make && sudo make install" > /dev/null 2>&1
echo "extension=mongo.so" >> $(php -r "echo php_ini_loaded_file();")