<?php

/**
 * Проверяем, что текущий пользователь - админ
 */
function admin_pre_process() {
    global $user;
    if (!$user || $user['role'] != ROLE_ADMIN) {
        redirect('/');
    }
}

function admin_index_action() {
    global $view_data;

    $view_data['head_title'] = _('Баланс системы');
    $view_data['system_user'] = get_user_by_id(SYSTEM_USER_ID);

    return render('admin/index.phtml', $view_data);
}