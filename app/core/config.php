<?php

global $config;

$config = array(
    'db' => array(
        'part1' => array(
            'host' => '127.0.0.1',
            'user' => 'root',
            'password' => '',
            'db_name' => 'vktest',
        ),
    ),
    'memcache' => array(
        array('127.0.0.1', 11211),
    ),
    'tables' => array(
        'users' => 'part1',
        'orders' => 'part1',
    ),
);