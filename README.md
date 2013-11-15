Phive Queue [![Build Status](https://secure.travis-ci.org/rybakit/phive-queue.png?branch=master)](http://travis-ci.org/rybakit/phive-queue)
===========

## Installation

The recommended way to install Phive Queue is through [composer](http://getcomposer.org).

Create a composer.json file inside your project directory:

``` json
{
    "require": {
        "rybakit/phive-queue": "*"
    }
}
```

Then run these two commands to install it:

``` bash
$ curl -sS https://getcomposer.org/installer | php
$ php composer.phar install
```

To use library, just add the following line to your code's bootstrap process:

``` php
<?php

require 'vendor/autoload.php';
```

## Drivers

Currently, there are the following drivers available:

* `MongoDbQueue`
* `RedisQueue`
* `PgsqlQueue`
* `MysqlQueue`
* `SqliteQueue`
* `InMemoryQueue`


## Usage example

``` php
<?php

$queue = new \Phive\Queue\InMemoryQueue();

$queue->push('item1');
$queue->push('item2', new \DateTime());
$queue->push('item3', time());
$queue->push('item4', '+5 seconds');
$queue->push('item5', 'next Monday');

// get the queue size
$count = $queue->count(); // $count = 5;

// iterate over a slice of items
foreach ($queue->slice(1, 2) as $item) {
    // $item = 'item2' ... 'item3';
}

// pop items off the queue
while ($item = $queue->pop()) {
    // $item = 'item1' ... 'item2' ... 'item3';
}

sleep(5);
$item = $queue->pop(); // $item = 'item4';

// clear the queue (will remove 'item5')
$queue->clear();
```


## Tests

To run the test suite, you'll have to install dependencies:

``` bash
$ ./tests/install_deps.sh
```

Once done, run unit tests:

``` bash
$ phpunit
```

To check performance run

``` bash
$ phpunit --group=benchmark
```

To check concurrency you'll have to install [Gearman Server](http://gearman.org) and [pecl/german extension](http://pecl.php.net/package/gearman).
After starting gearman server (gearmand) run as many workers as you need to test concurrency:

``` bash
$ php tests/worker.php
```

Then run the concurrency tests:

``` bash
$ phpunit --group=concurrency
```


## License

Phive Queue is released under the MIT License. See the bundled LICENSE file for details.
