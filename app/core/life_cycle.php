<?php

require APP_PATH . 'service/renderer.php';

function run($url, array $prev_urls = array()) {
    global $internal_redirect_url;
    $internal_redirect_url = false;

    // проверяем, что внутренний редирект не зациклился
    if (isset($prev_urls[$url])) {
        die('Повторный редирект на URL "' . $url . '"');
    }
    $prev_urls[$url] = 1;

    // разбиваем url на секции
    $url = reset(explode('?', $url));
    $sections = preg_split('/\//', $url, -1, PREG_SPLIT_NO_EMPTY);

    // получаем имя контроллера
    $controller_name = !empty($sections[0]) ? $sections[0] : 'index';
    // проверяем, что в имени контроллера нет ничего лишнего
    if (ctype_alnum($controller_name)) {
        // проверяем, что такой контроллер существует
        $controller_path = APP_PATH . 'controller/' . $controller_name . '.php';
        if (file_exists($controller_path)) {
            require_once $controller_path;
            // получаем имя экшена и проверяем, что в нем нет недопустимых символов
            $action_name = !empty($sections[1]) ? $sections[1] : 'index';
            if (ctype_alnum($action_name)) {
                // получаем имя функции-экшена и проверяем, что она существует
                $action_func = $controller_name . '_' . $action_name  . '_action';
                if (function_exists($action_func)) {
                    // если у контроллера есть функция pre_process, запускаем сначала ее
                    $pre_process_func = $controller_name . '_pre_process';
                    if (function_exists($pre_process_func)) {
                        $pre_process_func();
                        // проверяем, не было ли внутреннего редиректа
                        if ($internal_redirect_url) {
                            return run($internal_redirect_url, $prev_urls);
                        }
                    }
                    // запускаем экшен
                    $content = $action_func();
                    // проверяем, не было ли внутреннего редиректа
                    if ($internal_redirect_url) {
                        return run($internal_redirect_url, $prev_urls);
                    }
                } else {
                    // экшен не найден
                    $content = render_error(404, _('Страница не существует'));
                }
            } else {
                // недопустимое имя экшена
                $content = render_error(403);
            }
        } else {
            // контроллер не найден
            $content = render_error(404, _('Страница не существует'));
        }
    } else {
        // недопустимое имя контроллера
        $content = render_error(403);
    }

    global $view_no_render;
    if ($view_no_render) {
        // возвращаем контент "как есть"
        return $content;
    } else {
        // оборачиваем контент в лайаут
        global $view_data;
        $view_data['content_html'] = $content;
        return render('layout.phtml', $view_data);
    }
}