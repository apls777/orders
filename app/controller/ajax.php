<?php

/*
 * Авторизуем пользователя
 */
function ajax_auth_action() {
    $login = get_param('login');
    $pass = get_legacy_param('pass');

    $user = get_user_by_login($login);
    if (!$user) {
        return ajax_error(_('Неверный логин или пароль'));
    }

    if ($user['pass'] !== get_password_hash($pass)) {
        return ajax_error(_('Неверный логин или пароль'));
    }

    set_user($user);

    return ajax_success();
}

/*
 * Выходим с сайта
 */
function ajax_signOut_action() {
    clear_user();
    return ajax_success();
}

/*
 * Регистрируем пользователя
 */
function ajax_register_action() {
    $role = (int)get_param('reg_role');
    $login = get_param('reg_login');
    $pass = get_legacy_param('reg_pass');
    $passRetry = get_legacy_param('reg_pass_retry');

    if (!in_array($role, array(ROLE_CUSTOMER, ROLE_EXECUTOR))) {
        return ajax_error(_('Вы не указали роль'));
    }

    if (!ctype_alnum($login)) {
        return ajax_error(_('Логин может состоять только из цифр и букв английского алфавита'));
    }

    if (strlen($login) < 4) {
        return ajax_error(_('Логин не может быть короче 4-х символов'));
    }

    if (strlen($login) >= 30) {
        return ajax_error(_('Длина логина не может превышать 30 символов'));
    }

    $user = get_user_by_login($login);
    if ($user) {
        return ajax_error(_('Такой логин уже зарегистрирован в системе'));
    }

    if (!preg_match('/^(?=.*[A-Z])(?=.*[0-9])(?=.*[a-z]).{6}$/', $pass)) {
        return ajax_error(_('Слишком простой пароль'));
    }

    if ($pass !== $passRetry) {
        return ajax_error(_('Пароли не совпадают'));
    }

    // добавляем пользователя в таблицу
    $user_id = add_user($role, $login, $pass);
    if (!$user_id) {
        return ajax_error(_('Неизвестная ошибка'));
    }

    // сразу авторизуем его
    $user = get_user_by_id($user_id);
    if ($user) {
        set_user($user);
    }

    return ajax_success();
}