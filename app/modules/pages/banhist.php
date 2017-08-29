<?php
App::view(Setting::get('themes').'/index');

if (empty($_GET['uz'])) {
    $uz = check(App::getUsername());
} else {
    $uz = check(strval($_GET['uz']));
}

if (is_user()) {
    //show_title('История банов '.$uz);

    $total = DB::run() -> querySingle("SELECT COUNT(*) FROM `banhist` WHERE `user`=?;", [$uz]);
    $page = App::paginate(Setting::get('listbanhist'), $total);

    if ($total > 0) {

        $queryhist = DB::run() -> query("SELECT * FROM `banhist` WHERE user=? ORDER BY time DESC LIMIT ".$page['offset'].", ".Setting::get('listbanhist').";", [$uz]);

        while ($data = $queryhist -> fetch()) {
            echo '<div class="b">';
            echo '<i class="fa fa-history"></i> ';
            echo '<b>'.profile($data['user']).'</b> ('.date_fixed($data['time']).')</div>';

            echo '<div>';
            if (!empty($data['type'])) {
                echo 'Причина: '.App::bbCode($data['reason']).'<br>';
                echo 'Срок: '.formattime($data['term']).'<br>';
            }

            switch ($data['type']) {
                case '1': $stat = '<span style="color:#ff0000">Забанил</span>:';
                    break;
                case '2': $stat = '<span style="color:#ffa500">Изменил</span>:';
                    break;
                default: $stat = '<span style="color:#00cc00">Разбанил</span>:';
            }

            echo $stat.' '.profile($data['send']).'<br>';

            echo '</div>';
        }

        App::pagination($page);

        echo 'Всего действий: <b>'.$total.'</b><br><br>';
    } else {
        App::showError('В истории еще ничего нет!');
    }
} else {
    App::showError('Для просмотра истории необходимо авторизоваться');
}

echo '<i class="fa fa-arrow-circle-left"></i> <a href="/user/'.$uz.'">В анкету</a><br>';

App::view(Setting::get('themes').'/foot');
