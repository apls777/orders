<?php

require_once APP_PATH . 'dao/orders.php';

/**
 * Check that current user is a customer
 */
function customer_pre_process() {
    global $user;
    if (!$user || $user['role'] != ROLE_CUSTOMER) {
        redirect('/');
    }
}

function customer_index_action() {
    global $user, $view_data;

    $view_data['head_title'] = $view_data['page_title'] = _('My orders');

    // get orders created by user
    $orders = get_orders_by_customer_id($user['user_id']);

    // get executors logins
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

    $view_data['head_title'] = $view_data['page_title'] = _('Add an order');
    $view_data['token'] = generate_token();

    return render('customer/add.phtml', $view_data);
}

function customer_saveOrder_action() {
    $title = get_param('title');
    $description = get_param('desc');
    $cost = get_param('cost');

    if (!check_token(get_param('token'))) {
        return ajax_error(_('Wrong token. Please try to reload the page.'));
    }

    if (empty($title)) {
        return ajax_error(_('Order name is empty'));
    }

    if (empty($description)) {
        return ajax_error(_('Order description is empty'));
    }

    $cost = trim(str_replace(',', '.', $cost));
    if (!is_numeric($cost)) {
        return ajax_error(_('Price has wrong format'));
    }
    $cost = (float)$cost;

    if ($cost < 0) {
        return ajax_error(_('Price can\'t has a negative value'));
    }

    if ($cost < MIN_ORDER_COST) {
        return ajax_error(_('Price is too low'));
    }

    if ($cost > MAX_ORDER_COST) {
        return ajax_error(_('Price is too high'));
    }

    $parts = explode('.', $cost);
    if (isset($parts[1]) && strlen($parts[1]) > 2) {
        return ajax_error(_('Only 2 decimal points are allowed'));
    }

    global $user;
    $res = add_order($user['user_id'], $title, $description, $cost);
    if (!$res) {
        return ajax_error(_('Unknown error'));
    }

    return ajax_success();
}