<?php

include __DIR__ . '/../bootstrap.php';

spl_autoload_register(function($class) {
    $path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $file = __DIR__.'/handlers/'.$path.'.php';

    if (file_exists($file)) {
        require_once $file;

        return true;
    }

    return false;
});

$alias = empty($argv[1]) ? null : $argv[1];
$action = empty($argv[2]) ? null : $argv[2];

try {
    $handler = HandlerFactory::create($alias, getmypid());
    $handler->handle($action);
} catch (\Exception $e) {
    echo $e->getMessage(), PHP_EOL;
    exit(1);
}
