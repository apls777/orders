<?php

/*
 * Authorize user
 */
function ajax_auth_action() {
    $login = get_param('login');
    $pass = get_legacy_param('pass');

    $user = get_user_by_login($login);
    if (!$user) {
        return ajax_error(_('Login or password is incorrect'));
    }

    if ($user['pass'] !== get_password_hash($pass)) {
        return ajax_error(_('Login or password is incorrect'));
    }

    set_user($user);

    return ajax_success();
}

/*
 * Sign Out
 */
function ajax_signOut_action() {
    clear_user();
    return ajax_success();
}

/*
 * Register user
 */
function ajax_register_action() {
    $role = (int)get_param('reg_role');
    $login = get_param('reg_login');
    $pass = get_legacy_param('reg_pass');
    $passRetry = get_legacy_param('reg_pass_retry');

    if (!in_array($role, array(ROLE_CUSTOMER, ROLE_EXECUTOR))) {
        return ajax_error(_('You didn\'t state a role'));
    }

    if (!ctype_alnum($login)) {
        return ajax_error(_('Login can contain only english letters and numbers'));
    }

    if (strlen($login) < 4) {
        return ajax_error(_('Login length can\'t be shorter than 4 characters'));
    }

    if (strlen($login) >= 30) {
        return ajax_error(_('Login length can\'t be longer than 30 characters'));
    }

    $user = get_user_by_login($login);
    if ($user) {
        return ajax_error(_('Login already exists'));
    }

    if (!preg_match('/^(?=.*[A-Z])(?=.*[0-9])(?=.*[a-z]).{6}$/', $pass)) {
        return ajax_error(_('Password is too simple'));
    }

    if ($pass !== $passRetry) {
        return ajax_error(_('Passwords don\'t match'));
    }

    // add user to the table
    $user_id = add_user($role, $login, $pass);
    if (!$user_id) {
        return ajax_error(_('Unknown error'));
    }

    // authorize user
    $user = get_user_by_id($user_id);
    if ($user) {
        set_user($user);
    }

    return ajax_success();
}