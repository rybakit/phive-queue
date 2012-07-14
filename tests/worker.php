<?php

include __DIR__.'/bootstrap.php';

echo "Starting\n";

$worker = new \GearmanWorker();
$worker->addServer();

$queues = array();
$workerId = uniqid(getmypid().'_', true);
$worker->addFunction('pop', function(\GearmanJob $job) use (&$queues, $workerId) {
    static $i = 0;

    $workload = $job->workload();
    if (!isset($queues[$workload])) {
        $handler = unserialize($workload);
        $queues[$workload] = $handler->createQueue();
    }

    $item = $queues[$workload]->pop();

    echo ++$i.': popped ('.$item.') from '.get_class($queues[$workload])."\n";

    return json_encode(array($workerId, $item));
});

echo "Waiting for job...\n";
while ($worker->work()) {
    if (GEARMAN_SUCCESS != $worker->returnCode()) {
        echo 'return code: '.$worker->returnCode()."\n";
        break;
    }
}
