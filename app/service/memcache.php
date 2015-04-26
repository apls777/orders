<?php

function mc_init()
{
    global $memcache_pool, $config;

    $servers = $config['memcache'];
    $firstServer = array_shift($servers);
    $memcache_pool = memcache_connect($firstServer[0], $firstServer[1]);
    foreach ($servers as $server) {
        memcache_add_server($memcache_pool, $server[0], $server[1]);
    }
}

/**
 * @param string $key
 * @param mixed $value
 * @param int $ttl
 * @return bool
 */
function mc_set($key, $value, $ttl = 900)
{
    global $memcache_pool;
    return memcache_set($memcache_pool, $key, $value, 0, $ttl);
}

/**
 * @param string|array $key
 * @return mixed
 */
function mc_get($key)
{
    global $memcache_pool;
    return memcache_get($memcache_pool, $key);
}

/**
 * @param string $key
 * @return bool
 */
function mc_delete($key)
{
    global $memcache_pool;
    return memcache_delete($memcache_pool, $key);
}
