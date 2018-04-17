<?php

require __DIR__ . "/vendor/autoload.php";

use App\Kernel;

echo '<pre>';

$config = include('config.php');

new Kernel($config, false);

echo('</pre>');
echo 'ok';
