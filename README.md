Phive Queue
===========
[![Build Status](https://secure.travis-ci.org/rybakit/phive-queue.png?branch=master)](http://travis-ci.org/rybakit/phive-queue)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/rybakit/phive-queue/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/rybakit/phive-queue/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/rybakit/phive-queue/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/rybakit/phive-queue/?branch=master)

Phive Queue is a time-based scheduling queue with multiple backend support.


## Installation

The recommended way to install Phive Queue is through [Composer](http://getcomposer.org):

```sh
$ composer require rybakit/phive-queue:~0.5
```


## Usage example

```php
use Phive\Queue\InMemoryQueue;

$queue = new InMemoryQueue();

$queue->push('item1');
$queue->push('item2', new DateTime());
$queue->push('item3', time());
$queue->push('item4', '+5 seconds');
$queue->push('item5', 'next Monday');

// get the queue size
$count = $queue->count(); // 5

// pop items off the queue
// note that is not guaranteed that the items with the same scheduled time 
// will be recieved in the same order in which they were added
$item123 = $queue->pop();
$item123 = $queue->pop();
$item123 = $queue->pop();
$item4 = $queue->pop(); // throws Phive\Queue\NoItemAvailableException

sleep(5);
$item4 = $queue->pop();

// clear the queue (will remove 'item5')
$queue->clear();
```


## Queues

Currently, there are the following queues available:

* [MongoQueue](#mongoqueue)
* [RedisQueue](#redisqueue)
* [TarantoolQueue](#tarantoolqueue)
* [PheanstalkQueue](#pheanstalkqueue)
* [GenericPdoQueue](#genericpdoqueue)
* [SqlitePdoQueue](#sqlitepdoqueue)
* [SysVQueue](#sysvqueue)
* [InMemoryQueue](#inmemoryqueue)

#### MongoQueue

The `MongoQueue` requires the [Mongo PECL](http://pecl.php.net/package/mongo) extension *(v1.3.0 or higher)*.


*Note:* Before making use of the queue, it's highly recommended to create an index on a `eta` field:

```sh
$ mongo my_db --eval 'db.my_coll.ensureIndex({ eta: 1 })'
```

##### Constructor

```php
public MongoQueue::__construct(MongoClient $mongoClient, string $dbName, string $collName)
```

Parameters:

> <b>mongoClient</b>    The MongoClient instance<br>
> <b>dbName</b>         The database name<br>
> <b>collName</b>       The collection name<br>

##### Example

```php
use Phive\Queue\MongoQueue;

$client = new MongoClient();
$queue = new MongoQueue($client, 'my_db', 'my_coll');
```

#### RedisQueue

For the `RedisQueue` you have to install the [Redis PECL](http://pecl.php.net/package/redis) extension *(v2.2.3 or higher)*.

##### Constructor

```php
public RedisQueue::__construct(Redis $redis)
```

Parameters:

> <b>redis</b> The Redis instance<br>

##### Example

```php
use Phive\Queue\RedisQueue;

$redis = new Redis();
$redis->connect('127.0.0.1');
$redis->setOption(Redis::OPT_PREFIX, 'my_prefix:');

// Since the Redis client v2.2.5 the RedisQueue has the ability to utilize serialization:
// $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);

$queue = new RedisQueue($redis);
```

#### TarantoolQueue

##### Constructor

```php
public TarantoolQueue::__construct(Tarantool $tarantool, string $tubeName [, int $space = null ])
```

Parameters:

> <b>tarantool</b>  The Tarantool instance<br>
> <b>tubeName</b>   The tube name<br>
> <b>space</b>      <i>Optional</i>. The space number. Default to 0

##### Example

```php
use Phive\Queue\TarantoolQueue;

$tarantool = new Tarantool('127.0.0.1', 33013);
$queue = new TarantoolQueue($tarantool, 'my_tube');
```

#### PheanstalkQueue

The `PheanstalkQueue` requires the [Pheanstalk](https://github.com/pda/pheanstalk) library ([Beanstalk](http://kr.github.io/beanstalkd) client) to be installed:

```sh
$ composer require pda/pheanstalk:~2.1
```

##### Constructor

```php
public PheanstalkQueue::__construct(Pheanstalk_Pheanstalk $pheanstalk, string $tubeName)
```

Parameters:

> <b>pheanstalk</b> The Pheanstalk_Pheanstalk instance<br>
> <b>tubeName</b>   The tube name<br>

##### Example

```php
use Pheanstalk_Pheanstalk as Pheanstalk;
use Phive\Queue\PheanstalkQueue;

$pheanstalk = new Pheanstalk('127.0.0.1');
$queue = new PheanstalkQueue($pheanstalk, 'my_tube');
```

#### GenericPdoQueue

The `GenericPdoQueue` is intended for PDO drivers whose databases support stored procedures/functions (in fact all drivers except SQLite).

The `GenericPdoQueue` requires [PDO](http://php.net/pdo) and a [PDO driver](http://php.net/manual/en/pdo.drivers.php) for a particular database be installed.
On top of that PDO error mode must be set to throw exceptions (`PDO::ERRMODE_EXCEPTION`).

##### Constructor

```php
public GenericPdoQueue::__construct(PDO $pdo, string $tableName [, string $routineName = null ] )
```

Parameters:

> <b>pdo</b>         The PDO instance<br>
> <b>tableName</b>   The table name<br>
> <b>routineName</b> <i>Optional</i>. The routine name. Default to <b>tableName</b>_pop<br>

##### Example

```php
use Phive\Queue\Pdo\GenericPdoQueue;

$pdo = new PDO('pgsql:host=127.0.0.1;port=5432;dbname=my_db', 'db_user', 'db_pass');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$queue = new GenericPdoQueue($pdo, 'my_table', 'my_routine');
```

#### SqlitePdoQueue

The `SqlitePdoQueue` requires [PDO](http://php.net/pdo) and [SQLite PDO driver](http://php.net/manual/en/ref.pdo-sqlite.php).
On top of that PDO error mode must be set to throw exceptions (`PDO::ERRMODE_EXCEPTION`).


*Note:* For performance reasons it's highly recommended to activate [WALL mode](http://www.sqlite.org/wal.html):

```php
$pdo = new PDO('sqlite:/opt/databases/my_db.sq3');
$pdo->exec('PRAGMA journal_mode=WAL');
```

##### Constructor

```php
public SqlitePdoQueue::__construct(PDO $pdo, string $tableName)
```

Parameters:

> <b>pdo</b>       The PDO instance<br>
> <b>tableName</b> The table name<br>

##### Example

```php
use Phive\Queue\Pdo\SqlitePdoQueue;

$pdo = new PDO('sqlite:/opt/databases/my_db.sq3');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$queue = new SqlitePdoQueue($pdo, 'my_table');
```

#### SysVQueue

The `SysVQueue` requires PHP to be compiled with the option **--enable-sysvmsg**.

##### Constructor

```php
public SysVQueue::__construct(int $key [, bool $serialize = null [, int $perms = null ]] )
```

Parameters:

> <b>key</b>       The message queue numeric ID<br>
> <b>serialize</b> <i>Optional</i>. Whether to serialize an item or not. Default to false<br>
> <b>perms</b>     <i>Optional</i>. The queue permissions. Default to 0666<br>

##### Example

```php
use Phive\Queue\SysVQueue;

$queue = new SysVQueue(123456);
```

#### InMemoryQueue

As you might guess, the `InMemoryQueue` exists only in RAM and therefore operates faster than other queues. It can be useful in cases where the persistence is not necessary.

##### Constructor

```php
public InMemoryQueue::__construct()
```

##### Example

```php
use Phive\Queue\InMemoryQueue;

$queue = new InMemoryQueue();
```


## Exceptions

Every queue method declared in the `Queue` interface will throw an exception if a run-time error occurs at the time the method is called.

For example, in the code below, the `push()` call will fail with a `MongoConnectionException` exception in a case a remote server unreachable:

```php
use Phive\Queue\MongoQueue;

$queue = new MongoQueue(...);

// mongodb server goes down here

$queue->push('item'); // throws MongoConnectionException
```

But sometimes you may want to catch exceptions coming from a queue regardless of the underlying driver.
To do this just wrap your queue object with the `ExceptionalQueue` decorator:

```php
use Phive\Queue\ExceptionalQueue;
use Phive\Queue\MongoQueue;

$queue = new MongoQueue(...);
$queue = new ExceptionalQueue($queue);

// mongodb server goes down here

$queue->push('item'); // throws Phive\Queue\QueueException
```

And then, to catch queue level exceptions use the `QueueException`:

```php
use Phive\Queue\QueueException;

...

try {
    do_something_with_a_queue();
} catch (QueueException $e) {
    // handle queue exception
} catch (\Exception $e) {
    // handle base exception
}
```


## Tests

Phive Queue uses [PHPUnit](http://phpunit.de) for unit and acceptance testing. In order to run the tests, you'll first need to install the library dependencies using composer:

```sh
$ composer install
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
RedisQueue::push()
   Total operations:      1000
   Operations per second: 14031.762 [#/sec]
   Time per operation:    71.267 [ms]
   Time taken for test:   0.071 [sec]

RedisQueue::pop()
   Total operations:      1000
   Operations per second: 16869.390 [#/sec]
   Time per operation:    59.279 [ms]
   Time taken for test:   0.059 [sec]
.
RedisQueue::push() (delayed)
   Total operations:      1000
   Operations per second: 15106.226 [#/sec]
   Time per operation:    66.198 [ms]
   Time taken for test:   0.066 [sec]

RedisQueue::pop() (delayed)
   Total operations:      1000
   Operations per second: 14096.416 [#/sec]
   Time per operation:    70.940 [ms]
   Time taken for test:   0.071 [sec]
```

You may also change the number of items involved in the test by changing the `PHIVE_PERF_QUEUE_SIZE` value in your `phpunit.xml` file.


#### Concurrency

In order to check the concurrency you'll have to install the [Gearman](http://gearman.org) server and the [German PECL](http://pecl.php.net/package/gearman) extension.
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
