<?php

require __DIR__ . "/vendor/autoload.php";

use App\App;

echo '<pre>';

$config = include('config.php');

new App($config, false);

echo('</pre>');
echo 'ok';
