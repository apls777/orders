<?php

define('MKEY_USER', 'user');

require_once APP_PATH . 'service/db.php';

/**
 * Получаем пользователя по его ID
 *
 * @param $user_id
 * @return array|bool
 */
function get_user_by_id($user_id) {
    $users = get_users_by_ids(array($user_id));
    if (!isset($users[$user_id])) {
        return false;
    }

    return $users[$user_id];
}

/**
 * Получаем пользователя по его ID
 *
 * @param array $users_ids
 * @return array
 */
function get_users_by_ids(array $users_ids) {
    if (empty($users_ids)) {
        return array();
    }

    $users = array();
    $mkeys = array();
    foreach ($users_ids as $user_id) {
        $user_id = (int)$user_id;
        $mkeys[MKEY_USER . ':' . $user_id] = $user_id;
    }
    $res = mc_get(array_keys($mkeys));
    foreach ($res as $key => $value) {
        $users[$mkeys[$key]] = $value;
        unset($mkeys[$key]);
    }

    if (!empty($mkeys)) {
        $res = db_select('SELECT * FROM users WHERE user_id IN (' . implode(',', $mkeys) . ')', 'users');
        if (is_array($res)) {
            foreach ($res as $row) {
                $users[$row['user_id']] = $row;
                mc_set(MKEY_USER . ':' . $row['user_id'], $row);
            }
        }
    }

    return $users;
}

/**
 * Получаем пользователя по его логину
 *
 * @param $login
 * @return array|bool
 */
function get_user_by_login($login) {
    $res = db_select_row('SELECT * FROM users WHERE login = \'' . db_escape_string($login, 'users') . '\'', 'users');
    if (!$res) {
        return false;
    }

    return $res;
}

/**
 * Регистрируем нового пользователя
 *
 * @param $role
 * @param $login
 * @param $pass
 * @return bool|mysqli_result
 */
function add_user($role, $login, $pass) {
    $res = db_insert('users', array(
        'role' => $role,
        'login' => $login,
        'pass' => get_password_hash($pass),
        'inserted' => TIME,
    ));

    return db_insert_id('users');
}

function increase_user_balance($user_id, $amount) {

    $res = db_query('UPDATE users SET balance = balance + ' . $amount . ' WHERE user_id = ' . $user_id, 'users');
    mc_delete(MKEY_USER . ':' . $user_id);
    return $res;
}