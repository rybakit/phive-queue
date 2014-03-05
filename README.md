Phive Queue
===========
[![Build Status](https://secure.travis-ci.org/rybakit/phive-queue.png?branch=master)](http://travis-ci.org/rybakit/phive-queue)

Phive Queue is a time-based scheduling queue with multiple backends support.


## Installation

The recommended way to install Phive Queue is through [Composer](http://getcomposer.org).

To install the library run the following command:

```sh
$ php composer.phar require rybakit/phive-queue:*
```


## Queues

Currently, there are the following queues available:

* `MongoQueue`
* `RedisQueue`
* `GenericPdoQueue`
* `SqlitePdoQueue`
* `SysVQueue`
* `InMemoryQueue`

#### MongoQueue

```php
<?php

use Phive\Queue\Queue\MongoQueue;

$client = new MongoClient();
$queue = new MongoQueue($client, 'my_db', 'my_collection');
```

#### RedisQueue

```php
<?php

use Phive\Queue\Queue\RedisQueue;

$redis = new Redis();
$redis->connect('127.0.0.1');
$redis->setOption(Redis::OPT_PREFIX, 'my_prefix:');

$queue = new RedisQueue($redis);
```

#### GenericPdoQueue

```php
<?php

use Phive\Queue\Queue\Pdo\GenericPdoQueue;

$pdo = new PDO('pgsql:host=127.0.0.1;port=5432;dbname=foo', 'db_user', 'db_pass');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$queue = new GenericPdoQueue($pdo, 'my_table');
```

#### SqlitePdoQueue

```php
<?php

use Phive\Queue\Queue\Pdo\SqlitePdoQueue;

$pdo = new PDO('sqlite:/opt/databases/mydb.sq3');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$queue = new SqlitePdoQueue($pdo, 'my_table');
```

#### SysVQueue

```php
<?php

use Phive\Queue\Queue\SysVQueue;

$queue = new SysVQueue(123456);
```

#### InMemoryQueue

```php
<?php

use Phive\Queue\Queue\InMemoryQueue;

$queue = new InMemoryQueue();
```


## Usage example

```php
<?php

use Phive\Queue\Exception\NoItemAvailableException;
use Phive\Queue\Queue\InMemoryQueue;

$queue = new InMemoryQueue();

$queue->push('item1');
$queue->push('item2', new DateTime());
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

Every queue method declared in the `QueueInterface` interface will throw an exception if a run-time error occurs at the time the method is called.

For example, in the code below, the `push()` call will fail with a `MongoConnectionException` exception in a case a remote server unreachable:

```php
<?php

use Phive\Queue\Queue\MongoQueue;

$queue = new MongoQueue(...);

// mongodb server goes down here

$queue->push('item'); // throws MongoConnectionException
```

But sometimes you may want to catch exceptions coming from a queue regardless of the underlying driver.
To do this just wrap your queue object with the `ExceptionalQueue` decorator:

```php
<?php

use Phive\Queue\Queue\ExceptionalQueue;
use Phive\Queue\Queue\MongoQueue;

$queue = new MongoQueue(...);
$queue = new ExceptionalQueue($queue);

// mongodb server goes down here

$queue->push('item'); // throws Phive\Queue\Exception\RuntimeException
```

And then, to catch queue level exceptions use `ExceptionInterface` [marker interface](http://en.wikipedia.org/wiki/Marker_interface_pattern):

```php
<?php

use Phive\Queue\Exception\ExceptionInterface;

...

try {
    do_something_with_a_queue();
} catch (ExceptionInterface $e) {
    // handle queue exception
} catch (\Exception $e) {
    // handle base exception
}
```


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
