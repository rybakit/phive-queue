phive-queue
===========

[![Build Status](https://secure.travis-ci.org/rybakit/phive-queue.png?branch=master)](http://travis-ci.org/rybakit/phive-queue)


## Tests

To run the test suite, you'll have to install dependencies:

    ./tests/install_deps.sh

Once done, run unit tests:

    phpunit

To check performance run

    phpunit --group=benchmark

To check concurrency you'll have to install [Gearman Server](http://gearman.org) and [pecl/german extension](http://pecl.php.net/package/gearman).
After starting gearman server (gearmand) run as many workers as you need to test concurrency:

    php tests/worker.php

Then run the concurrency tests:

    phpunit --group=concurrency


## License

See the LICENSE file.
