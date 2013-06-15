<?php

include __DIR__.'/bootstrap.php';

echo "Starting\n";

$worker = new \GearmanWorker();
$worker->addServer();

$workerId = uniqid(getmypid().'_', true);
$worker->addFunction('pop', function(\GearmanJob $job) use ($workerId) {
    static $i = 0;
    static $queues = array();

    $workload = $job->workload();
    if (!isset($queues[$workload])) {
        $handler = unserialize($workload);
        $queues[$workload] = $handler->createQueue();
    }

    $item = $queues[$workload]->pop();

    echo ++$i.': popped ('.$item.') from '.get_class($queues[$workload])."\n";

    return $workerId.':'.$item;
});

echo "Waiting for a job...\n";
while ($worker->work()) {
    if (GEARMAN_SUCCESS != $worker->returnCode()) {
        echo $worker->error()."\n";
        exit(1);
    }
}
