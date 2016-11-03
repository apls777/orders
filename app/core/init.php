<?php

define('TIME', time());
define('SESSION_KEY_UID', 'uid');

define('ROLE_ADMIN', 1); // administrators
define('ROLE_CUSTOMER', 2); // customers
define('ROLE_EXECUTOR', 3); // executors

define('SYSTEM_USER_ID', 1); // user without role to keep a system balance
define('PROJECT_PERCENT', 10); // percent which system gets from each order
define('MIN_PROJECT_COMMISSION', '0.01'); // minimal system commission
define('MIN_ORDER_COST', '0.02'); // minimal order price
define('MAX_ORDER_COST', 1000000000); // maximal order price
define('LIMIT_ALLOWED_ORDERS', 30); // number of orders items per page

require APP_PATH . 'core/config.php';
require APP_PATH . 'core/functions.php';
require APP_PATH . 'service/memcache.php';
require APP_PATH . 'service/user.php';

session_start();
mc_init();

// current user
global $user;
$user = get_user();

// data for templates
global $view_data;
$view_data = array(
    'head_title' => '',
);

// don't render layout if we don't need it
global $view_no_render;
$view_no_render = false;

// CSS and script URLs which will be added to the site head
global $head_scripts, $head_css;
$head_scripts = $head_css = array();