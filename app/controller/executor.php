<?php

require_once APP_PATH . 'dao/orders.php';

/**
 * Проверяем, что текущий пользователь - исполнитель
 */
function executor_pre_process() {
    global $user;
    if (!$user || $user['role'] != ROLE_EXECUTOR) {
        redirect('/');
    }
}

function executor_index_action() {
    global $view_data;

    $view_data['head_title'] = $view_data['page_title'] = _('Доступные заказы');

    // получаем последние доступные заказы
    $orders = get_allowed_orders();

    // получаем логины заказчиков
    $customers_ids = array_column($orders, 'customer_id');
    $customers = get_users_by_ids($customers_ids);
    foreach ($orders as &$order) {
        $order['customer_login'] = isset($customers[$order['customer_id']]) ? $customers[$order['customer_id']]['login'] : '';
    }

    $view_data['orders'] = $orders;
    $view_data['token'] = generate_token();

    return render('executor/index.phtml', $view_data);
}

function executor_completed_action() {
    global $user, $view_data;

    $view_data['head_title'] = $view_data['page_title'] = _('Выполненные заказы');

    // получаем выполненные пользователем заказы
    $orders = get_orders_by_executor_id($user['user_id']);

    // получаем логины заказчиков
    $customers_ids = array_column($orders, 'customer_id');
    $customers = get_users_by_ids($customers_ids);
    foreach ($orders as &$order) {
        $order['customer_login'] = isset($customers[$order['customer_id']]) ? $customers[$order['customer_id']]['login'] : '';
    }

    $view_data['orders'] = $orders;

    return render('executor/completed.phtml', $view_data);
}

function executor_completeOrder_action() {
    global $user;
    $order_id = (int)get_param('order_id');

    if (!check_token(get_param('token'))) {
        return ajax_error(_('Неверный токен. Попробуйте перезагрузить страницу.'));
    }

    $res = complete_order($order_id, $user['user_id'], $user_earned);
    if (!$res) {
        return ajax_error('', array(
            'html' => render('popups/complete_error.phtml'),
            'button_title' => _('Заказ недоступен'),
        ));
    }

    return ajax_success(array(
        'new_balance' => number_format($user['balance'] + $user_earned, 2, '.', ''),
        'html' => render('popups/complete_success.phtml'),
        'button_title' => _('Заказ выпонен'),
    ));
}