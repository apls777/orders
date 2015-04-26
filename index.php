<?php

set_time_limit(5);
date_default_timezone_set('Europe/Moscow');

define('APP_PATH', 'app/');

require APP_PATH . 'core/init.php';
require APP_PATH . 'core/life_cycle.php';

$content = run($_SERVER['REQUEST_URI']);

echo $content;