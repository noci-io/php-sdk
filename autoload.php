<?php

/**
 * Auto-loads Digital'ize SDK classes
 */
spl_autoload_register(function ($class) {
    if (substr($class, 0, 15) !== 'Digitalize\\SDK\\')
        return;
    $class = str_replace('\\', '/', $class);
    $path = __DIR__ . '/src/' . substr($class, 15) . '.php';
    if (file_exists($path))
        require($path);
});
