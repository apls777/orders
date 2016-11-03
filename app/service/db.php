<?php

global $db_connections;
$db_connections = array();

function db_connect($table) {
    global $config, $db_connections;

    $db_server = isset($config['tables'][$table]) ? $config['tables'][$table] : reset(array_keys($config['db']));

    if (!isset($db_connections[$db_server])) {
        $db_config = $config['db'][$db_server];
        $db_conn = mysqli_connect($db_config['host'], $db_config['user'], $db_config['password'], $db_config['db_name']);
        if (mysqli_connect_errno() !== 0) {
            die(_('Can\'t connect to DB'));
        }
        mysqli_query($db_conn, 'SET CHARACTER SET utf8');
        $db_connections[$db_server] = $db_conn;
    }

    return $db_connections[$db_server];
}

function db_query($sql, $table) {
    $result = mysqli_query(db_connect($table), $sql);
    if ($result === false) {
        trigger_error(_('SQL Error: "') . $sql . '"', E_ERROR);
    }

    return $result;
}

function db_insert($table, array $data, $ignore = false) {
    if (empty($data)) {
        return false;
    }

    $values = array();
    foreach($data as $value) {
        $values[] = db_escape_string($value, $table);
    }

    $sql = 'INSERT ' . ($ignore ? 'IGNORE ' : '') . 'INTO `' . $table . '` (`' . implode('`,`', array_keys($data)) . '`) VALUES (\'' . implode('\',\'', $values) . '\')';

    $query = db_query($sql, $table);
    if (!$query) {
        trigger_error(_('Insert error'), E_ERROR);
    }

    return $query;
}

function db_update($table, array $data, $where = false) {
    if (empty($data)) {
        return false;
    }

    $sets = array();
    foreach($data as $key => $value) {
        $sets[] = $key . '=\'' . db_escape_string($value, $table) . '\'';
    }
    $sets = implode(',', $sets);
    $sql = 'UPDATE `' . $table . '` SET ' . $sets;
    if ($where) {
        $sql .= ' WHERE ' . $where;
    }

    $query = db_query($sql, $table);
    if (!$query) {
        trigger_error(_('Update error'), E_ERROR);
    }

    return $query;
}

function db_insert_id($table) {
    return mysqli_insert_id(db_connect($table));
}

function db_affected_rows($table) {
    return mysqli_affected_rows(db_connect($table));
}

/**
 * Get all rows
 *
 * @param $sql
 * @param $table
 * @return array
 */
function db_select($sql, $table) {
    $res = array();
    $query = db_query($sql, $table);
    while ($row = mysqli_fetch_assoc($query)) {
        $res[] = $row;
    }

    return $res;
}

/**
 * Get only first row
 *
 * @param $sql
 * @param $table
 * @return array|null
 */
function db_select_row($sql, $table) {
    return mysqli_fetch_assoc(db_query($sql, $table));
}

/**
 * Begin a transaction
 */
function db_begin($table) {
    db_query('BEGIN', $table);
}

/**
 * Commit a transaction
 */
function db_commit($table) {
    db_query('COMMIT', $table);
}

/**
 * Rollback a transaction
 */
function db_rollback($table) {
    db_query('ROLLBACK', $table);
}

function db_escape_string($str, $table) {
    return mysqli_real_escape_string(db_connect($table), $str);
}