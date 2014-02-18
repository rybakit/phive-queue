<?php

include __DIR__.'/bootstrap.php';

echo "Starting\n";

$worker = new \GearmanWorker();
$worker->addServer();

$workerId = uniqid(getmypid().'_', true);
$worker->addFunction('pop', function(\GearmanJob $job) use ($workerId) {
    static $i = 0;
    static $queues = [];

    $workload = $job->workload();
    if (!isset($queues[$workload])) {
        $handler = unserialize($workload);
        $queues[$workload] = $handler->createQueue();
    }

    $item = $queues[$workload]->pop();

    printf("%d: %s -> %s\n", ++$i, get_class($queues[$workload]), $item);

    return $workerId.':'.$item;
});

echo "Waiting for a job...\n";
while ($worker->work()) {
    if (GEARMAN_SUCCESS != $worker->returnCode()) {
        echo $worker->error()."\n";
        exit(1);
    }
}
