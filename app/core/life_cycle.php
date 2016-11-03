<?php

require APP_PATH . 'service/renderer.php';

function run($url, array $prev_urls = array()) {
    global $internal_redirect_url;
    $internal_redirect_url = false;

    // check that internal redirect has no infinite loop
    if (isset($prev_urls[$url])) {
        die('Repeated redirection for "' . $url . '"');
    }
    $prev_urls[$url] = 1;

    // split URL by sections
    $url = explode('?', $url)[0];
    $sections = preg_split('/\//', $url, -1, PREG_SPLIT_NO_EMPTY);

    // get controller name
    $controller_name = !empty($sections[0]) ? $sections[0] : 'index';
    // check controller name
    if (ctype_alnum($controller_name)) {
        // check that controller exists
        $controller_path = APP_PATH . 'controller/' . $controller_name . '.php';
        if (file_exists($controller_path)) {
            require_once $controller_path;
            // get action name and check it
            $action_name = !empty($sections[1]) ? $sections[1] : 'index';
            if (ctype_alnum($action_name)) {
                // check that action function exists
                $action_func = $controller_name . '_' . $action_name  . '_action';
                if (function_exists($action_func)) {
                    // call a "pre_process" function if it exists
                    $pre_process_func = $controller_name . '_pre_process';
                    if (function_exists($pre_process_func)) {
                        $pre_process_func();
                        // check if there was an internal redirect
                        if ($internal_redirect_url) {
                            return run($internal_redirect_url, $prev_urls);
                        }
                    }
                    // call action function
                    $content = $action_func();
                    // check if there was an internal redirect
                    if ($internal_redirect_url) {
                        return run($internal_redirect_url, $prev_urls);
                    }
                } else {
                    // action was not found
                    $content = render_error(404, _('Page doesn\'t exist'));
                }
            } else {
                // wrong action name
                $content = render_error(403);
            }
        } else {
            // controller was not found
            $content = render_error(404, _('Page doesn\'t exist'));
        }
    } else {
        // wrong controller name
        $content = render_error(403);
    }

    global $view_no_render;
    if ($view_no_render) {
        // return raw content
        return $content;
    } else {
        // render content with layout
        global $view_data;
        $view_data['content_html'] = $content;
        return render('layout.phtml', $view_data);
    }
}