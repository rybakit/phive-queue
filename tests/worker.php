<?php

include __DIR__.'/bootstrap.php';

use Phive\Queue\Pdo\GenericPdoQueue;

$worker = new \GearmanWorker();
$worker->addServer();

$workerId = uniqid(getmypid().'_', true);
$worker->addFunction('pop', function(\GearmanJob $job) use ($workerId) {
    static $i = 0;

    $handler = unserialize($job->workload());
    $queue = $handler->createQueue();

    $queueName = get_class($queue);
    if ($queue instanceof GenericPdoQueue) {
        $queueName .= '#'.$handler->getDriverName();
    }

    $item = $queue->pop();

    printf("%s: %s item #%s\n",
        str_pad(++$i, 4, ' ', STR_PAD_LEFT),
        str_pad($queueName.' ', 50, '.'),
        $item
    );

    return $workerId.':'.$item;
});

echo "Waiting for a job...\n";
while ($worker->work()) {
    if (GEARMAN_SUCCESS !== $worker->returnCode()) {
        echo $worker->error()."\n";
        exit(1);
    }
}
