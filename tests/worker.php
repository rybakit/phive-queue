<?php

include __DIR__.'/bootstrap.php';

$worker = new \GearmanWorker();
$worker->addServer();

$workerId = uniqid(getmypid().'_', true);
$worker->addFunction('pop', function(\GearmanJob $job) use ($workerId) {
    static $i = 0;

    $handler = unserialize($job->workload());
    $queue = $handler->createQueue();
    $item = $queue->pop();

    printf("%d: %s -> %s\n", ++$i, get_class($queue), $item);

    return $workerId.':'.$item;
});

echo "Waiting for a job...\n";
while ($worker->work()) {
    if (GEARMAN_SUCCESS != $worker->returnCode()) {
        echo $worker->error()."\n";
        exit(1);
    }
}
