<?php
App::view($config['themes'].'/index');

if (!empty($params['page'])){

    $page = check($params['page']);

    if (! preg_match('|^[a-z0-9_\-/]+$|i', $page)) {
        App::abort('default', 'Недопустимое название страницы!');
    }

    $file = explode('/', $page);

    if (empty($file[1])){
        $page = $page.'/index';
    }

    if (! file_exists(APP.'/modules/files/'.$page.'.dat')) {
        App::abort('default', 'Ошибка! Данной страницы не существует!');
    }

    include_once (APP.'/modules/files/'.$page.'.dat');

} else {
	include_once (STORAGE.'/main/files.dat');
}

App::view($config['themes'].'/foot');
