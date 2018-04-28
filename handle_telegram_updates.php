<?php

require __DIR__ . "/vendor/autoload.php";

use App\Kernel;

echo '<pre>';

$config = include('config.php');

$kernel = new Kernel($config);
$kernel->doTelegramUpdates();

echo('</pre>');
echo 'ok';
