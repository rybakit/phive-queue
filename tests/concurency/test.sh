#!/bin/bash

if [ -z $1 ]; then
    echo Handler name is required.
    exit 1
fi

DIR="$(cd `dirname $0` && pwd)"
SCRIPT="php $DIR/handle.php $1" #redis, mongo, pdo_pgsql, pdo_mysql
CONCURRENCY=4

$SCRIPT prepare &&
seq 4 | xargs -n 1 -P $CONCURRENCY $SCRIPT push &&
$SCRIPT status &&
seq 4 | xargs -n 1 -P $CONCURRENCY $SCRIPT pop &&
$SCRIPT status &&
$SCRIPT shutdown