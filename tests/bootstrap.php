<?php

spl_autoload_register(function($class) {
    $path = str_replace('\\', DIRECTORY_SEPARATOR, $class);

    foreach (array('src', 'tests') as $dirPrefix) {
        $file = __DIR__.'/../'.$dirPrefix.'/'.$path.'.php';
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }

    return false;
});
