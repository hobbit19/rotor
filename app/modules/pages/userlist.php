<?php
view(setting('themes').'/index');

if (isset($_GET['act'])) {
    $act = check($_GET['act']);
} else {
    $act = 'index';
}
if (isset($_GET['uz'])) {
    $uz = check($_GET['uz']);
} elseif (isset($_POST['uz'])) {
    $uz = check($_POST['uz']);
} else {
    $uz = "";
}
$page = abs(intval(Request::input('page', 1)));

//show_title('Список пользователей');

switch ($action):
############################################################################################
##                                    Вывод пользователей                                 ##
############################################################################################
    case 'index':

        $total = DB::run() -> querySingle("SELECT count(*) FROM `users`;");
        $page = paginate(setting('userlist'), $total);

        if ($total > 0) {

            $queryusers = DB::select("SELECT * FROM `users` ORDER BY `point` DESC, `login` ASC LIMIT ".$page['offset'].", ".setting('userlist').";");

            $i = 0;
            while ($data = $queryusers -> fetch()) {
                ++$i;

                echo '<div class="b"> ';
                echo '<div class="img">'.userAvatar($data['login']).'</div>';

                if ($uz == $data['login']) {
                    echo ($page['offset'] + $i).'. <b><big>'.profile($data['login'], '#ff0000').'</big></b> ';
                } else {
                    echo ($page['offset'] + $i).'. <b>'.profile($data['login']).'</b> ';
                }
                echo '('.plural($data['point'], setting('scorename')).')<br>';
                echo userStatus($data['login']).' '.userOnline($data['login']);
                echo '</div>';

                echo '<div>';
                echo 'Форум: '.$data['allforum'].' | Гостевая: '.$data['allguest'].' | Коммент: '.$data['allcomments'].'<br>';
                echo 'Посещений: '.$data['visits'].'<br>';
                echo 'Деньги: '.userMoney($data['login']).'<br>';
                echo 'Дата регистрации: '.dateFixed($data['joined'], 'j F Y').'</div>';
            }

            pagination($page);

            echo '<div class="form">';
            echo '<b>Поиск пользователя:</b><br>';
            echo '<form action="/userlist?act=search&amp;page='.$page['current'].'" method="post">';
            echo '<input type="text" name="uz" value="'.getUser('login').'">';
            echo '<input type="submit" value="Искать"></form></div><br>';

            echo 'Всего пользователей: <b>'.$total.'</b><br><br>';
        } else {
            showError('Пользователей еще нет!');
        }
    break;

    ############################################################################################
    ##                                  Поиск пользователя                                    ##
    ############################################################################################
    case 'search':

        if (!empty($uz)) {
            $queryuser = DB::run() -> querySingle("SELECT `login` FROM `users` WHERE LOWER(`login`)=? LIMIT 1;", [strtolower($uz)]);

            if (!empty($queryuser)) {
                $queryrating = DB::select("SELECT `login` FROM `users` ORDER BY `point` DESC, `login` ASC;");
                $ratusers = $queryrating -> fetchAll(PDO::FETCH_COLUMN);

                foreach ($ratusers as $key => $ratval) {
                    if ($queryuser == $ratval) {
                        $rat = $key + 1;
                    }
                }

                if (!empty($rat)) {
                    $end = ceil($rat / setting('userlist'));

                    setFlash('success', 'Позиция в рейтинге: '.$rat);
                    redirect("/userlist?page=$end&uz=$queryuser");
                } else {
                    showError('Пользователь с данным логином не найден!');
                }
            } else {
                showError('Пользователь с данным логином не зарегистрирован!');
            }
        } else {
            showError('Ошибка! Вы не ввели логин пользователя');
        }

        echo '<i class="fa fa-arrow-circle-left"></i> <a href="/userlist?page='.$page.'">Вернуться</a><br>';
    break;

endswitch;

echo '<i class="fa fa-users"></i> <a href="/onlinewho">Новички</a><br>';

view(setting('themes').'/foot');
