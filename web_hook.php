<?php

require __DIR__ . "/vendor/autoload.php";

$config = include('config.php');

$kernel = new \App\Kernel($config, true);
$kernel->doTelegramWebHook();
