<?php

require __DIR__ . "/vendor/autoload.php";

use App\App;

$config = include('config.php');

new App($config, true);
