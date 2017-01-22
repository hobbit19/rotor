<?php
App::view($config['themes'].'/index');

if (isset($_GET['act'])) {
    $act = check($_GET['act']);
} else {
    $act = 'index';
}
if (isset($_GET['id'])) {
    $id = abs(intval($_GET['id']));
} else {
    $id = 0;
}
$page = abs(intval(Request::input('page', 1)));

if (is_admin([101, 102, 103])) {
    show_title('Управление голосованием');

    switch ($act):
    ############################################################################################
    ##                                    Главная страница                                    ##
    ############################################################################################
        case 'index':

            $queryvote = DB::run() -> query("SELECT * FROM `vote` WHERE `closed`=? ORDER BY `time` DESC;", [0]);
            $votes = $queryvote -> fetchAll();

            if (count($votes) > 0) {
                foreach($votes as $valvote) {
                    echo '<div class="b">';
                    echo '<i class="fa fa-bar-chart"></i> <b><a href="/votes?act=poll&amp;id='.$valvote['id'].'">'.$valvote['title'].'</a></b><br />';
                    echo '<a href="/admin/votes?act=edit&amp;id='.$valvote['id'].'">Изменить</a>';
                    echo ' / <a href="/admin/votes?act=action&amp;do=close&amp;id='.$valvote['id'].'&amp;uid='.$_SESSION['token'].'">Закрыть</a>';

                    if (is_admin([101])) {
                        echo ' / <a href="/admin/votes?act=del&amp;id='.$valvote['id'].'&amp;uid='.$_SESSION['token'].'" onclick="return confirm(\'Вы подтверждаете удаление голосования?\')">Удалить</a>';
                    }

                    echo '</div>';

                    echo '<div>Создано: '.date_fixed($valvote['time']).'<br />';
                    echo 'Всего голосов: '.$valvote['count'].'</div>';
                }
                echo '<br />';
            } else {
                show_error('Открытых голосований еще нет!');
            }

            echo '<i class="fa fa-bar-chart"></i> <a href="/admin/votes?act=new">Создать голосование</a><br />';
            echo '<i class="fa fa-briefcase"></i> <a href="/admin/votes?act=history">История голосований</a><br />';

            if (is_admin([101])) {
                echo '<i class="fa fa-arrow-circle-up"></i> <a href="/admin/votes?act=rest&amp;uid='.$_SESSION['token'].'">Пересчитать</a><br />';
            }

        break;

        ############################################################################################
        ##                                      Создание                                          ##
        ############################################################################################
        case 'new':

            echo '<div class="form">';
            echo '<form action="/admin/votes?act=add&amp;uid='.$_SESSION['token'].'" method="post">';

            echo 'Вопрос:<br />';
            echo '<input type="text" name="title" size="50" maxlength="100" /><br />';
            echo 'Ответ 1:<br /><input type="text" name="answer[]" maxlength="50" /><br />';
            echo 'Ответ 2:<br /><input type="text" name="answer[]" maxlength="50" /><br />';
            echo 'Ответ 3:<br /><input type="text" name="answer[]" maxlength="50" /><br />';
            echo 'Ответ 4:<br /><input type="text" name="answer[]" maxlength="50" /><br />';
            echo 'Ответ 5:<br /><input type="text" name="answer[]" maxlength="50" /><br />';
            echo 'Ответ 6:<br /><input type="text" name="answer[]" maxlength="50" /><br />';
            echo 'Ответ 7:<br /><input type="text" name="answer[]" maxlength="50" /><br />';
            echo 'Ответ 8:<br /><input type="text" name="answer[]" maxlength="50" /><br />';
            echo 'Ответ 9:<br /><input type="text" name="answer[]" maxlength="50" /><br />';
            echo 'Ответ 10:<br /><input type="text" name="answer[]" maxlength="50" /><br />';
            echo '<input type="submit" value="Создать" /></form></div><br />';

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/votes">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                      Создание                                          ##
        ############################################################################################
        case 'add':

            $uid = check($_GET['uid']);
            $title = check($_POST['title']);
            $answer = check($_POST['answer']);

            if ($uid == $_SESSION['token']) {
                if (utf_strlen($title) >= 3 && utf_strlen($title) <= 100) {
                    $answer = array_diff($answer, ['']);

                    if (count($answer) > 1) {
                        DB::run() -> query("INSERT INTO `vote` (`title`, `time`) VALUES (?, ?);", [$title, SITETIME]);
                        $lastid = DB::run() -> lastInsertId();

                        $dbr = DB::run() -> prepare("INSERT INTO `voteanswer` (`vote_id`, `answer`) VALUES (?, ?);");

                        foreach ($answer as $data) {
                            $dbr -> execute($lastid, $data);
                        }

                        notice('Голосование успешно создано!');
                        redirect("/admin/votes");
                    } else {
                        show_error('Ошибка! Необходимо минимум 2 варианта ответов!');
                    }
                } else {
                    show_error('Ошибка! Слишком длинный или короткий вопрос (от 3 до 100 символов)!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/votes?act=new">Вернуться</a><br />';
            echo '<i class="fa fa-bar-chart"></i> <a href="/admin/votes">К голосованиям</a><br />';
        break;

        ############################################################################################
        ##                                   Редактирование                                       ##
        ############################################################################################
        case 'edit':

            $votes = DB::run() -> queryFetch("SELECT * FROM `vote` WHERE `id`=? LIMIT 1;", [$id]);

            if (!empty($votes)) {
                echo '<div class="form">';
                echo '<form action="/admin/votes?act=change&amp;id='.$id.'&amp;uid='.$_SESSION['token'].'" method="post">';

                echo 'Вопрос:<br />';
                echo '<input type="text" name="title" size="50" maxlength="100" value="'.$votes['title'].'" /><br />';

                $queryanswer = DB::run() -> query("SELECT * FROM `voteanswer` WHERE `vote_id`=? ORDER BY `id`;", [$id]);
                $answer = $queryanswer -> fetchAll();

                for ($i = 0; $i < 10; $i++) {
                    if (!empty($answer[$i])) {
                        echo '<span style="color:#ff0000">Ответ '.($i + 1).':</span><br /><input type="text" name="answer['.$answer[$i]['id'].']" maxlength="50" value="'.$answer[$i]['answer'].'" /><br />';
                    } else {
                        echo 'Ответ '.($i + 1).':<br /><input type="text" name="newanswer[]" maxlength="50" /><br />';
                    }
                }

                echo '<input type="submit" value="Изменить" /></form></div><br />';

                echo 'Поля отмеченные красным цветом обязательны для заполнения!<br /><br />';
            } else {
                show_error('Ошибка! Данного голосования не существует!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/votes">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                   Редактирование                                       ##
        ############################################################################################
        case 'change':

            $uid = check($_GET['uid']);
            $title = check($_POST['title']);
            $answer = check($_POST['answer']);

            if ($uid == $_SESSION['token']) {
                if (utf_strlen($title) >= 3 && utf_strlen($title) <= 100) {
                    $queryvote = DB::run() -> querySingle("SELECT `id` FROM `vote` WHERE `id`=? LIMIT 1;", [$id]);
                    if (!empty($queryvote)) {
                        if (!in_array('', $answer)) {
                            DB::run() -> query("UPDATE `vote` SET `title`=? WHERE `id`=?;", [$title, $id]);

                            $dbr = DB::run() -> prepare("UPDATE `voteanswer` SET `answer`=? WHERE `id`=?;");
                            foreach ($answer as $key => $data) {
                                $dbr -> execute($data, $key);
                            }

                            if (isset($_POST['newanswer'])) {
                                $newanswer = check($_POST['newanswer']);
                                $newanswer = array_diff($newanswer, ['']);
                                if (count($newanswer) > 0) {
                                    $dbr = DB::run() -> prepare("INSERT INTO `voteanswer` (`vote_id`, `answer`) VALUES (?, ?);");
                                    foreach ($newanswer as $data) {
                                        $dbr -> execute($id, $data);
                                    }
                                }
                            }

                            notice('Голосование успешно изменено!');
                            redirect("/admin/votes");
                        } else {
                            show_error('Ошибка! Не заполнены все обязательные поля с ответами!');
                        }
                    } else {
                        show_error('Ошибка! Данного голосования не существует!');
                    }
                } else {
                    show_error('Ошибка! Слишком длинный или короткий вопрос (от 3 до 100 символов)!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/votes?act=edit&amp;id='.$id.'">Вернуться</a><br />';
            echo '<i class="fa fa-bar-chart"></i> <a href="/admin/votes">К голосованиям</a><br />';
        break;

        ############################################################################################
        ##                                      Закрытие                                          ##
        ############################################################################################
        case 'action':

            $uid = check($_GET['uid']);
            $do = check($_GET['do']);

            if ($uid == $_SESSION['token']) {
                if ($do == 'close' || $do == 'open') {
                    $queryvote = DB::run() -> querySingle("SELECT `id` FROM `vote` WHERE `id`=? LIMIT 1;", [$id]);
                    if (!empty($queryvote)) {
                        if ($do == 'close') {
                            DB::run() -> query("UPDATE `vote` SET `closed`=? WHERE `id`=?;", [1, $id]);
                            DB::run() -> query("DELETE FROM `votepoll` WHERE `vote_id`=?;", [$id]);
                            notice('Голосование успешно закрыто!');
                            redirect("/admin/votes");
                        }

                        if ($do == 'open') {
                            DB::run() -> query("UPDATE `vote` SET `closed`=? WHERE `id`=?;", [0, $id]);
                            notice('Голосование успешно открыто!');
                            redirect("/admin/votes?act=history");
                        }
                    } else {
                        show_error('Ошибка! Данного голосования не существует!');
                    }
                } else {
                    show_error('Ошибка! Не выбрано действие для голосования!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/votes">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                      Удаление                                          ##
        ############################################################################################
        case 'del':

            $uid = check($_GET['uid']);

            if ($uid == $_SESSION['token']) {
                if (is_admin([101])) {
                    $queryvote = DB::run() -> querySingle("SELECT `id` FROM `vote` WHERE `id`=? LIMIT 1;", [$id]);
                    if (!empty($queryvote)) {
                        DB::run() -> query("DELETE FROM `vote` WHERE `id`=?;", [$id]);
                        DB::run() -> query("DELETE FROM `voteanswer` WHERE `vote_id`=?;", [$id]);
                        DB::run() -> query("DELETE FROM `votepoll` WHERE `vote_id`=?;", [$id]);

                        notice('Голосование успешно удалено!');
                        redirect("/admin/votes");
                    } else {
                        show_error('Ошибка! Данного голосования не существует!');
                    }
                } else {
                    show_error('Ошибка! Удалять голосования могут только суперадмины!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/votes">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                    Пересчет счетчиков                                  ##
        ############################################################################################
        case 'rest':
            $uid = check($_GET['uid']);
            if ($uid == $_SESSION['token']) {
                if (is_admin([101])) {
                    DB::run() -> query("UPDATE `vote` SET `count`=(SELECT SUM(`result`) FROM `voteanswer` WHERE `vote`.id=`voteanswer`.`vote_id`) WHERE `closed`=?;", [0]);

                    notice('Все данные успешно пересчитаны!');
                    redirect("/admin/votes");
                } else {
                    show_error('Ошибка! Пересчитывать голосования могут только суперадмины!');
                }
            } else {
                show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
            }

            echo '<i class="fa fa-arrow-circle-left"></i> <a href="/admin/votes">Вернуться</a><br />';
        break;

        ############################################################################################
        ##                                          История                                      ##
        ############################################################################################
        case 'history':

            $total = DB::run() -> querySingle("SELECT count(*) FROM `vote` WHERE `closed`=? ORDER BY `time`;", [1]);
            $page = App::paginate(App::setting('allvotes'), $total);

            if ($total > 0) {

                $queryvote = DB::run() -> query("SELECT * FROM `vote` WHERE `closed`=? ORDER BY `time` DESC LIMIT ".$page['offset'].", ".$config['allvotes'].";", [1]);

                while ($data = $queryvote -> fetch()) {
                    echo '<div class="b">';
                    echo '<i class="fa fa-briefcase"></i> <b><a href="/votes/history?act=result&amp;id='.$data['id'].'&amp;page='.$page['current'].'">'.$data['title'].'</a></b><br />';

                    echo '<a href="/admin/votes?act=action&amp;do=open&amp;id='.$data['id'].'&amp;uid='.$_SESSION['token'].'">Открыть</a>';

                    if (is_admin([101])) {
                        echo ' / <a href="/admin/votes?act=del&amp;id='.$data['id'].'&amp;uid='.$_SESSION['token'].'" onclick="return confirm(\'Вы подтверждаете удаление голосования?\')">Удалить</a>';
                    }

                    echo '</div>';
                    echo '<div>Создано: '.date_fixed($data['time']).'<br />';
                    echo 'Всего голосов: '.$data['count'].'</div>';
                }

                App::pagination($page);
            } else {
                show_error('Голосований в архиве еще нет!');
            }

            echo '<i class="fa fa-bar-chart"></i> <a href="/admin/votes">Список голосований</a><br />';
        break;

    endswitch;

    echo '<i class="fa fa-wrench"></i> <a href="/admin">В админку</a><br />';

} else {
    redirect("/");
}

App::view($config['themes'].'/foot');
