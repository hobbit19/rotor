<?php
$page = check(param('page'));

if (!empty($page)){

    if (! preg_match('|^[a-z0-9_\-/]+$|i', $page)) {
        abort('default', 'Недопустимое название страницы!');
    }

    $file = explode('/', $page);

    if (empty($file[1])){
        $page = $page.'/index';
    }

    if (! file_exists(RESOURCES.'/views/files/'.$page.'.blade.php')) {
        abort('default', 'Ошибка! Данной страницы не существует!');
    }

    return view('files/layout', compact('page'));
} else {
    return view('files/index');
}
