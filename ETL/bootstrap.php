<?php
require __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => '127.0.0.1',
    'port'      => 3306,
    'database'  => 'bi_products',
    'username'  => 'root',
    'password'  => '1234',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
], 'source');

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => '127.0.0.1',
    'port'      => 3306,
    'database'  => 'bi_project',
    'username'  => 'root',
    'password'  => '1234',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
], 'target');

$capsule->setAsGlobal();
$capsule->bootEloquent();
