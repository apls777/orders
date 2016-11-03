<?php

define('PASSWORD_SALT', 'SQRAZ26zRSNBZe1s');

require APP_PATH . 'dao/user.php';

/**
 * Get authorized user
 *
 * @return array|bool
 */
function get_user() {
    $user = false;
    if (isset($_SESSION[SESSION_KEY_UID])) {
        $user = get_user_by_id($_SESSION[SESSION_KEY_UID]);
    }

    return $user;
}

/**
 * Authorize user
 *
 * @param array $newUser
 */
function set_user(array $newUser) {
    global $user;
    $user = $newUser;
    $_SESSION[SESSION_KEY_UID] = $user['user_id'];
}

/**
 * Clear user session
 */
function clear_user() {
    if (isset($_SESSION[SESSION_KEY_UID])) {
        global $user;
        $user = false;
        unset($_SESSION[SESSION_KEY_UID]);
    }
}

function get_password_hash($pass) {
    return md5($pass . ':' . PASSWORD_SALT);
}