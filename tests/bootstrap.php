<?php

exec('echo "" > ' . __DIR__.'/../storage/logs/laravel.log');

require_once __DIR__.'/../bootstrap/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$app['Illuminate\Contracts\Console\Kernel']->call('migrate');
