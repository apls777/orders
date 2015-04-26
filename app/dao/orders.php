<?php

define('MKEY_ALLOWD_ORDERS', 'allowed_orders');

/**
 * Получаем доступные заказы
 *
 * @param $limit
 * @param $lastOrderId
 * @return array
 */
function get_allowed_orders($limit = LIMIT_ALLOWED_ORDERS, $lastOrderId = 0) {
    $limit = (int)$limit;
    $lastOrderId = (int)$lastOrderId;
    $mkey = MKEY_ALLOWD_ORDERS . ':' . $limit;

    // пробуем достать первую страницу из кэша
    if (!$lastOrderId) {
        $res = mc_get($mkey);
        if (is_array($res)) {
            return $res;
        }
    }

    // получаем доступные для выполнения заказы
    $sql = 'SELECT * FROM orders WHERE executor_id = 0';
    if ($lastOrderId) {
        $sql .= ' AND order_id < ' . $lastOrderId;
    }
    $sql .= ' ORDER BY order_id DESC LIMIT ' . $limit;
    $res = db_select($sql, 'orders');
    if (!is_array($res)) {
        $res = array();
    }

    // если это первая страница, кэшируем ее
    if (!$lastOrderId) {
        mc_set(MKEY_ALLOWD_ORDERS, $res);
    }

    return $res;
}

/**
 * Получаем все заказы созданные заказиком
 * Кэширование здесь не нужно
 *
 * @param $customer_id
 * @return array
 */
function get_orders_by_customer_id($customer_id) {
    $customer_id = (int)$customer_id;
    $res = db_select('SELECT * FROM orders WHERE customer_id = ' . $customer_id . ' ORDER BY inserted DESC', 'orders');
    if (!$res) {
        return array();
    }

    return $res;
}

/**
 * Получаем все заказы выполненные исполнителем
 * Кэширование здесь не нужно
 *
 * @param $executor_id
 * @return array
 */
function get_orders_by_executor_id($executor_id) {
    $executor_id = (int)$executor_id;
    $res = db_select('SELECT * FROM orders WHERE executor_id = ' . $executor_id . ' ORDER BY completed DESC', 'orders');
    if (!$res) {
        return array();
    }

    return $res;
}

/**
 * Получаем заказ по его ID
 *
 * @param $order_id
 * @return array|bool
 */
function get_order_by_id($order_id) {
    $order_id = (int)$order_id;
    $res = db_select_row('SELECT * FROM orders WHERE order_id = ' . $order_id, 'orders');
    if (!$res) {
        return false;
    }

    return $res;
}

/**
 * Добавляем новый заказ
 *
 * @param $customer_id
 * @param $title
 * @param $description
 * @param $cost
 * @return bool|mysqli_result
 */
function add_order($customer_id, $title, $description, $cost) {
    $res = db_insert('orders', array(
        'customer_id' => $customer_id,
        'title' => $title,
        'description' => $description,
        'cost' => $cost,
        'inserted' => TIME,
    ));

    // чистим кэш доступных заказов
    mc_delete(MKEY_ALLOWD_ORDERS . ':' . LIMIT_ALLOWED_ORDERS);

    return $res;
}

/**
 * Выполнение заказа
 *
 * @param $order_id
 * @param $executor_id
 * @param bool $user_earned
 * @return bool
 */
function complete_order($order_id, $executor_id, &$user_earned = false) {
    // транзакцию делаем в базе, где лежит табличка users
    $table = 'users';
    db_begin($table);

    // выполняем заказ
    $res = db_update('orders', array(
        'executor_id' => $executor_id,
        'completed' => TIME,
    ), 'order_id = ' . (int)$order_id . ' AND executor_id = 0');
    if (!$res) {
        db_rollback($table);
        return false;
    }

    // проверяем, что заказ был выполнен
    $res = db_affected_rows('orders');
    if (!$res) {
        db_rollback($table);
        return false;
    }

    // получаем информацию о заказе
    $order = get_order_by_id($order_id);
    if (!$order) {
        db_rollback($table);
        return false;
    }

    // обновляем баланс пользователя
    bcscale(2);
    $project_earned = (string)round($order['cost'] * (PROJECT_PERCENT / 100), 2, PHP_ROUND_HALF_EVEN);
    $user_earned = bcsub($order['cost'], $project_earned);
    $res = increase_user_balance($executor_id, $user_earned);
    if (!$res) {
        db_rollback($table);
        return false;
    }

    // обновляем баланс системы
    $res = increase_user_balance(SYSTEM_USER_ID, $project_earned);
    if (!$res) {
        db_rollback($table);
        return false;
    }

    // чистим кэш доступных заказов
    mc_delete(MKEY_ALLOWD_ORDERS . ':' . LIMIT_ALLOWED_ORDERS);

    db_commit($table);
    return true;
}