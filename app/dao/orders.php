<?php

define('MKEY_ALLOWD_ORDERS', 'allowed_orders');

/**
 * Get available orders
 *
 * @param $limit
 * @param $lastOrderId
 * @return array
 */
function get_allowed_orders($limit = LIMIT_ALLOWED_ORDERS, $lastOrderId = 0) {
    $limit = (int)$limit;
    $lastOrderId = (int)$lastOrderId;
    $mkey = MKEY_ALLOWD_ORDERS . ':' . $limit;

    // try to get first page from cache
    if (!$lastOrderId) {
        $res = mc_get($mkey);
        if (is_array($res)) {
            return $res;
        }
    }

    // get orders which should be completed
    $sql = 'SELECT * FROM orders WHERE executor_id = 0';
    if ($lastOrderId) {
        $sql .= ' AND order_id < ' . $lastOrderId;
    }
    $sql .= ' ORDER BY order_id DESC LIMIT ' . $limit;
    $res = db_select($sql, 'orders');
    if (!is_array($res)) {
        $res = array();
    }

    // if it's a first page, cache it
    if (!$lastOrderId) {
        mc_set(MKEY_ALLOWD_ORDERS, $res);
    }

    return $res;
}

/**
 * Get all orders which were created by customer.
 * We don't need a cache here.
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
 * Get all orders which were completed by executor.
 * We don't need a cache here.
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
 * Get order by ID
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
 * Add new order
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

    // clear cache for available orders
    mc_delete(MKEY_ALLOWD_ORDERS . ':' . LIMIT_ALLOWED_ORDERS);

    return $res;
}

/**
 * Complete an order
 *
 * @param $order_id
 * @param $executor_id
 * @param bool $user_earned
 * @return bool
 */
function complete_order($order_id, $executor_id, &$user_earned = false) {
    // start a transaction for DB with users table
    $table = 'users';
    db_begin($table);

    // complete an order
    $res = db_update('orders', array(
        'executor_id' => $executor_id,
        'completed' => TIME,
    ), 'order_id = ' . (int)$order_id . ' AND executor_id = 0');
    if (!$res) {
        db_rollback($table);
        return false;
    }

    // check that an order was completed
    $res = db_affected_rows('orders');
    if (!$res) {
        db_rollback($table);
        return false;
    }

    // get an order info
    $order = get_order_by_id($order_id);
    if (!$order) {
        db_rollback($table);
        return false;
    }

    bcscale(2);
    // an order price can't be lower than MIN_ORDER_COST
    if (bccomp($order['cost'], MIN_ORDER_COST) === -1) {
        db_rollback($table);
        return false;
    }

    // get commission amount
    $project_earned = bcmul($order['cost'], bcdiv(PROJECT_PERCENT, 100));
    if (bccomp($project_earned, MIN_PROJECT_COMMISSION) === -1) {
        $project_earned = MIN_PROJECT_COMMISSION;
    }

    // update user balance
    $user_earned = bcsub($order['cost'], $project_earned);
    $res = increase_user_balance($executor_id, $user_earned);
    if (!$res) {
        db_rollback($table);
        return false;
    }

    // update system balance
    $res = increase_user_balance(SYSTEM_USER_ID, $project_earned);
    if (!$res) {
        db_rollback($table);
        return false;
    }

    // clear cache for available orders
    mc_delete(MKEY_ALLOWD_ORDERS . ':' . LIMIT_ALLOWED_ORDERS);

    db_commit($table);
    return true;
}