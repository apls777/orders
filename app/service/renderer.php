<?php

function render($template, $data = array()) {
    $template_path = APP_PATH . 'templates/' . $template;
    if (file_exists($template_path)) {
        ob_start();
        include $template_path;
        $html = ob_get_clean();
    } else {
        $html = render_error(404, _('Шаблон не найден'));
    }

    return $html;
}

function render_error($code, $log = '') {
    $config = array(
        403 => array(
            'title' => _('Доступ запрещен'),
            'header' => 'HTTP/1.1 403 Forbidden',
        ),
        404 => array(
            'title' => _('Страница не найдена'),
            'header' => 'HTTP/1.0 404 Not Found',
        ),
    );
    if (isset($headers[$code])) {
        global $view_data;
        $view_data['head_title'] = $config[$code]['title'];
        header($headers[$code]['header']);
    }

    return render('errors/' . $code . '.phtml');
}