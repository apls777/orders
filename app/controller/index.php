<?php

/*
 * Login and registration page
 */
function index_index_action() {
    global $user, $view_data;

    $view_data['head_title'] = _('Sing In / Registration');

    // if user is already authorized, redirect him to personal area
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