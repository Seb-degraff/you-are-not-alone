<?php

require __DIR__ . "/vendor/autoload.php";

use App\App;

echo '<pre>';

$config = include('config.php');

new App($config);

//$result = \Longman\TelegramBot\Request::sendMessage(['chat_id' => '-252465736', 'text' => 'Next Turn!']);

echo('</pre>');
echo 'ok';
