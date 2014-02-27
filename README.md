Phive Queue
===========
[![Build Status](https://secure.travis-ci.org/rybakit/phive-queue.png?branch=master)](http://travis-ci.org/rybakit/phive-queue)

Phive Queue is a time-based scheduling queue with multiple backends support.


## Installation

The recommended way to install Phive Queue is through [composer](http://getcomposer.org).

Create a composer.json file inside your project directory:

```json
{
    "require": {
        "rybakit/phive-queue": "*"
    }
}
```

Then run these two commands to install it:

```sh
$ curl -sS https://getcomposer.org/installer | php
$ php composer.phar install
```

To use library, just add the following line to your code's bootstrap process:

```php
<?php

require 'vendor/autoload.php';
```


## Queues

TODO: Describe each queue (usage, requirements, limitations)

Currently, there are the following queues available:

* `MongoQueue`
* `RedisQueue`
* `GenericPdoQueue`
* `SqlitePdoQueue`
* `SysVQueue`
* `InMemoryQueue`


## Usage example

```php
<?php

use Phive\Queue\Exception\NoItemAvailableException;
use Phive\Queue\Queue\InMemoryQueue;

$queue = new InMemoryQueue();

$queue->push('item1');
$queue->push('item2', new \DateTime());
$queue->push('item3', time());
$queue->push('item4', '+5 seconds');
$queue->push('item5', 'next Monday');

// get the queue size
$count = $queue->count(); // $count = 5;

// pop items off the queue
try {
    while (1) {
        $item = $queue->pop(); // $item = 'item1', 'item2', 'item3';
    }
} catch (NoItemAvailableException $e) {
    // no items are available
    ...
}

sleep(5);
$item = $queue->pop(); // $item = 'item4';

// clear the queue (will remove 'item5')
$queue->clear();
```

## Exceptions

TODO: Describe exception handling


## Tests

Phive Queue uses PHPUnit for unit and acceptance testing. In order to run the tests, you'll first need to install the library dependencies using composer:

```sh
php composer.phar install
```

You can then run the tests:

```sh
$ phpunit
```

You may also wish to specify your own default values of some tests (db names, passwords, queue sizes, etc.).
Just create your own `phpunit.xml` file by copying the `phpunit.xml.dist` file and customize to your needs.


#### Performance

To check the performance of queues run:

```sh
$ phpunit --group performance
```

This test inserts a number of items (1000 by default) into a queue, and then retrieves them back.
It measures the average time for `push` and `pop` operations and outputs the resulting stats, e.g.:

```sh
Phive\Queue\Queue\SysVQueue::push()
   Total operations:      1000
   Operations per second: 67149.691 [#/sec]
   Time per operation:    14.892 [ms]
   Time taken for test:   0.015 [sec]

Phive\Queue\Queue\SysVQueue::pop()
   Total operations:      1000
   Operations per second: 96908.667 [#/sec]
   Time per operation:    10.319 [ms]
   Time taken for test:   0.010 [sec]
```

You may also change the number of items involved in the test by changing the `PHIVE_PERF_QUEUE_SIZE` value in your `phpunit.xml` file.


#### Concurrency

In order to check the concurrency you'll have to install the [Gearman server](http://gearman.org) and the [pecl/german](http://pecl.php.net/package/gearman) extension.
Once the server has been installed and started, create a number of processes (workers) by running:

```sh
$ php tests/worker.php
```

Then run the tests:

```sh
$ phpunit --group concurrency
```

This test inserts a number of items (100 by default) into a queue, and then each worker tries to retrieve them back in parallel.

You may also change the number of items involved in the test by changing the `PHIVE_CONCUR_QUEUE_SIZE` value in your `phpunit.xml` file.


## License

Phive Queue is released under the MIT License. See the bundled LICENSE file for details.
