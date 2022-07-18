<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use Rocket\Options;
use Rocket\Main;

date_default_timezone_set('Asia/Tokyo');

$options = new Options();

$main = new Main($options);
$main->run();
