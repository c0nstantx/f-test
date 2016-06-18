<?php
/**
 * A very simple autoloader
 */
$srcDir = __DIR__.'/';
$baseNamespace = 'Foodora\\';

spl_autoload_register(function($className) use ($srcDir, $baseNamespace) {
    $className = str_replace($baseNamespace, '', $className);
    $classFile = $srcDir.(str_replace('\\', DIRECTORY_SEPARATOR, $className)).'.php';
    if (!file_exists($classFile)) {
        throw new \RuntimeException("File '$classFile' could not be found.");
    }

    require_once $classFile;
});

/**
 * It is not safe to rely on the system's timezone settings. If no timezone
 * is defined in php.ini setup UTC timezone
 */
if (!ini_get('date.timezone')) {
    ini_set('date.timezone', 'UTC');
}