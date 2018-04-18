<?php

require __DIR__ . "/vendor/autoload.php";

$config = include('config.php');

new \App\Kernel($config, true);
