<?php

require_once APP_PATH . 'dao/orders.php';

/**
 * Проверяем, что текущий пользователь - заказчик
 */
function customer_pre_process() {
    global $user;
    if (!$user || $user['role'] != ROLE_CUSTOMER) {
        redirect('/');
    }
}

function customer_index_action() {
    global $user, $view_data;

    $view_data['head_title'] = $view_data['page_title'] = _('Мои заказы');

    // получаем созданные пользователем заказы
    $orders = get_orders_by_customer_id($user['user_id']);

    // получаем логины исполнителей
    $executors_ids = array_column($orders, 'executor_id');
    $executors = get_users_by_ids($executors_ids);
    foreach ($orders as &$order) {
        $order['executor_login'] = isset($executors[$order['executor_id']]) ? $executors[$order['executor_id']]['login'] : '';
    }

    $view_data['orders'] = $orders;
    $view_data['is_new'] = get_param('new');

    return render('customer/index.phtml', $view_data);
}

function customer_add_action() {
    global $view_data;
    $view_data['head_title'] = $view_data['page_title'] = _('Добавление заказа');

    return render('customer/add.phtml');
}

function customer_saveOrder_action() {
    $title = get_param('title');
    $description = get_param('desc');
    $cost = get_param('cost');

    if (empty($title)) {
        return ajax_error(_('Вы не заполнили имя заказа'));
    }

    if (empty($description)) {
        return ajax_error(_('Вы не заполнили описание заказа'));
    }

    $cost = trim(str_replace(',', '.', $cost));
    if (!is_numeric($cost)) {
        return ajax_error(_('Некорректное значение стоимости'));
    }

    if ($cost > MAX_ORDER_COST) {
        return ajax_error(_('Слишком большая стоимость заказа'));
    }

    global $user;
    $res = add_order($user['user_id'], $title, $description, $cost);
    if (!$res) {
        return ajax_error(_('Неизвестная ошибка'));
    }

    return ajax_success();
}