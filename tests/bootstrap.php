<?php

use Venta\Console\Command;

if (!class_exists('Composer\Autoload\ClassLoader', false)) {
    require __DIR__ . '/../vendor/autoload.php';
}

abstract class MockCommand extends Command
{
}