<?php

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespace('Dumper', __DIR__);
$loader->register();
