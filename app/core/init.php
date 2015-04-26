<?php

define('TIME', time());
define('SESSION_KEY_UID', 'uid');

define('ROLE_ADMIN', 1); // администраторы
define('ROLE_CUSTOMER', 2); // заказчики
define('ROLE_EXECUTOR', 3); // исполнители

define('SYSTEM_USER_ID', 1); // в этом пользователе хранится баланс системы, у него нет роли и под ним нельзя зайти
define('PROJECT_PERCENT', 10); // процент, который получает система с каждого заказа
define('LIMIT_ALLOWED_ORDERS', 30); // кол-во заказов на одной странице (для исполнителей)
define('MAX_ORDER_COST', 1000000000); // максимальная стоимость заказа

require APP_PATH . 'core/config.php';
require APP_PATH . 'core/functions.php';
require APP_PATH . 'service/memcache.php';
require APP_PATH . 'service/user.php';

session_start();
mc_init();

// получаем текущего пользователя
global $user;
$user = get_user();

// данные для представления
global $view_data;
$view_data = array(
    'head_title' => '',
);

// не рендерим лайаут, если он не нужен
global $view_no_render;
$view_no_render = false;

// css'ки и скрипты, которые надо добавить в шапку
global $head_scripts, $head_css;
$head_scripts = $head_css = array();