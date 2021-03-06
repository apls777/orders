<?php

/**
 * Returns URL for static file
 *
 * @param $path
 * @return string
 */
function get_static_url($path) {
    return '/static/' . $path;
}

/**
 * Add a script URL to site head
 *
 * @param $path
 */
function append_script($path) {
    global $head_scripts;
    $head_scripts[$path] = get_static_url($path);
}

/**
 * Add a CSS URL to site head
 *
 * @param $path
 */
function append_css($path) {
    global $head_css;
    $head_css[$path] = get_static_url($path);
}

/**
 * External redirect
 *
 * @param $url
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * Internal redirect
 *
 * @param $url
 */
function internal_redirect($url) {
    global $internal_redirect_url;
    $internal_redirect_url = $url;
}

function get_param($name, $default = false) {
    $value = get_legacy_param($name, $default);
    return is_string($value) ? htmlspecialchars($value) : $value;
}

function get_legacy_param($name, $default = false) {
    if (!isset($_REQUEST[$name])) {
        return $default;
    }

    return $_REQUEST[$name];
}

/**
 * Generate CSRF token
 *
 * @return string
 */
function generate_token() {
    $token = md5(microtime(true) . mt_rand(0, 1000000000));
    setcookie('token', $token, null, '/');
    return $token;
}

/**
 * Check CSRF token
 *
 * @param $token
 * @return bool
 */
function check_token($token) {
    $c_token = isset($_COOKIE['token']) ? $_COOKIE['token'] : '';
    return ($token === $c_token);
}

function ajax_response(array $data, $message = '') {
    global $view_no_render;
    $view_no_render = true;

    header('Content-Type: application/json');

    if ($message) {
        $data['message'] = $message;
    }

    return json_encode($data);
}

function ajax_success(array $data = array(), $message = '') {
    $data['ret'] = 1;
    return ajax_response($data, $message);
}

function ajax_error($message = '', array $data = array()) {
    $data['ret'] = 0;
    return ajax_response($data, $message);
}