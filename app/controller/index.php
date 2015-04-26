<?php

/*
 * Страница логина и регистрации
 */
function index_index_action() {
    global $user, $view_data;

    $view_data['head_title'] = _('Вход');

    // если пользователь авторизован, редиректим его в свой кабинет
    if ($user) {
        $urls = array(
            ROLE_ADMIN => '/admin/',
            ROLE_CUSTOMER => '/customer/',
            ROLE_EXECUTOR => '/executor/',
        );
        if (isset($urls[$user['role']])) {
            internal_redirect($urls[$user['role']]);
            return false;
        }
    }

    return render('index/sing_in.phtml');
}