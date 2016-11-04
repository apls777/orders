<?php

function mc_init() {
    global $memcached, $config;

    $memcached = new Memcached();
    foreach ($config['memcache'] as $server) {
        $memcached->addServer($server[0], $server[1]);
    }
}

/**
 * @param string $key
 * @param mixed $value
 * @param int $ttl
 * @return bool
 */
function mc_set($key, $value, $ttl = 900) {
    /** @var Memcached $memcached */
    global $memcached;
    return $memcached->set($key, $value, $ttl);
}

/**
 * @param string|array $key
 * @return mixed
 */
function mc_get($key) {
    /** @var Memcached $memcached */
    global $memcached;
    return $memcached->get($key);
}

/**
 * @param string $key
 * @return bool
 */
function mc_delete($key) {
    /** @var Memcached $memcached */
    global $memcached;
    return $memcached->delete($key);
}
