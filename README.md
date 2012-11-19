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
$ curl -s http://getcomposer.org/installer | php
$ php composer.phar install
```

To use library, just add the following line to your code's bootstrap process:

``` php
<?php

require 'vendor/autoload.php';
```

## Drivers

Currently, there are the following drivers:

* `MongoDbQueue`
* `RedisQueue`
* `PgsqlQueue`
* `MysqlQueue`
* `SqliteQueue`
* `InMemoryQueue`


## Usage

``` php
<?php

use Phive\Queue\InMemoryQueue;

$queue = new InMemoryQueue();

$queue->push('payload1');
$queue->push('payload2', new \DateTime());
$queue->push('payload3', time());
$queue->push('payload4', '+5 seconds');

foreach ($queue->peek(2, 1) as $payload) {
    echo $payload, PHP_EOL;
}

while ($payload = $queue->pop()) {
    echo $payload, PHP_EOL;
}

$queue->clear();

echo $queue->count(), PHP_EOL;

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
