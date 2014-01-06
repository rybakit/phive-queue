<?php

spl_autoload_register(function($class) {
    if ('Phive\\Queue\\' === substr($class, 0, 12)) {
        $class = substr($class, 12);
    }
    if ('Tests\\' === substr($class, 0, 6)) {
        $class = substr($class, 6);
    }
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
