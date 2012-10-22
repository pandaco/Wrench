#!/usr/bin/env php
<?php

require(__DIR__ . '/../lib/SplClassLoader.php');

$wrenchLoader = new SplClassLoader('Wrench', __DIR__ . '/../lib');
$wrenchLoader->register();

$pandacoLoader = new SplClassLoader('Pandaco', __DIR__ . '/../lib');
$pandacoLoader->register();

$server = new \Wrench\Server('ws://127.0.0.1:8000/');
$server->registerApplication('demo', new \Pandaco\Application\Demo());
$server->run();
