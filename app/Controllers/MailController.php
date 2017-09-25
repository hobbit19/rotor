<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validation;
use App\Models\User;

class MailController extends BaseController
{
    /**
     * Главная страница
     */
    public function index()
    {
        if (Request::isMethod('post')) {

            $message = nl2br(check(Request::input('message')));
            $name    = check(Request::input('name'));
            $email   = check(Request::input('email'));
            $protect = check(Request::input('protect'));

            if (getUser()) {
                $name = getUser('login');
                $email = getUser('email');
            }

            $validation = new Validation();

            $validation->addRule('equal', [$protect, $_SESSION['protect']], ['protect' => 'Проверочное число не совпало с данными на картинке!'])
                ->addRule('string', $name, ['name' => 'Слишком длинное или короткое имя'], true, 5, 100)
                ->addRule('string', $message, ['message' => 'Слишком длинное или короткое сообшение'], true, 5, 50000)
                ->addRule('email', $email, ['email' => 'Неправильный адрес email, необходим формат name@site.domen!'], true);

            if ($validation->run()) {

                $message .= '<br><br>IP: ' . getClientIp() . '<br>Браузер: ' . getUserAgent() . '<br>Отправлено: ' . dateFixed(SITETIME, 'j.m.Y / H:i');

                $subject = 'Письмо с сайта ' . setting('title');
                $body = view('mailer.default', compact('subject', 'message'), true);
                sendMail(env('SITE_EMAIL'), $subject, $body, ['from' => [$email => $name]]);

                setFlash('success', 'Ваше письмо успешно отправлено!');
                redirect("/");

            } else {
                setFlash('danger', $validation->getErrors());
            }
        }

        return view('mail/index');
    }

    /**
     * Восстановление пароля
     */
    public function recovery()
    {
        if (getUser()) {
            abort('default', 'Вы авторизованы, восстановление пароля невозможно!');
        }

        $cookieLogin = (isset($_COOKIE['login'])) ? check($_COOKIE['login']) : '';

        if (Request::isMethod('post')) {
            $login = check(Request::input('user'));
            $protect = check(Request::input('protect'));

            $user = User::query()->where('login', $login)->orWhere('email', $login)->first();
            if (! $user) {
                abort('default', 'Пользователь с данным логином или email не найден!');
            }

            $validation = new Validation();

            $validation->addRule('equal', [$protect, $_SESSION['protect']], 'Проверочное число не совпало с данными на картинке!')
                ->addRule('min', [$user['timepasswd'], SITETIME], 'Восстанавливать пароль можно не чаще чем раз в 12 часов!');

            if ($validation->run()) {
                $resetKey  = str_random();
                $resetLink = siteLink(setting('home')) . '/recovery/restore?key=' . $resetKey;

                $user->update([
                    'keypasswd'  => $resetKey,
                    'timepasswd' => SITETIME + 43200,
                ]);

                //Инструкция по восстановлению пароля на email
                $subject = 'Восстановление пароля на сайте ' . setting('title');
                $message = 'Здравствуйте, ' . $user['login'] . '<br>Вами была произведена операция по восстановлению пароля на сайте <a href="' . setting('home') . '">' . setting('title') . '</a><br><br>Данные отправителя:<br>Ip: ' . getClientIp() . '<br>Браузер: ' . getUserAgent() . '<br>Отправлено: ' . date('j.m.Y / H:i', SITETIME) . '<br><br>Для того чтобы восстановить пароль, вам необходимо нажать на кнопку восстановления<br>Если это письмо попало к вам по ошибке или вы не собираетесь восстанавливать пароль, то просто проигнорируйте его';

                $body = view('mailer.recovery', compact('subject', 'message', 'resetLink'), true);
                sendMail($user['email'], $subject, $body);

                setFlash('success', 'Восстановление пароля инициализировано!');
                redirect('/recovery');
            } else {
                setFlash('danger', $validation->getErrors());
            }
        }

        return view('mail/recovery', compact('cookieLogin'));
    }

    /**
     * Восстановление пароля
     */
    public function restore()
    {
        if (getUser()) {
            abort('default', 'Вы авторизованы, восстановление пароля невозможно!');
        }

        $key = check(Request::input('key'));

        $user = User::query()->where('keypasswd', $key)->first();
        if (! $user) {
            abort('default', 'Ключ для восстановления недействителен!');
        }

        $validation = new Validation();

        $validation->addRule('not_empty', $key, 'Отсутствует секретный код в ссылке для восстановления пароля!')
            ->addRule('not_empty', $user['keypasswd'], 'Данный пользователь не запрашивал восстановление пароля!')
            ->addRule('max', [$user['timepasswd'], SITETIME], 'Секретный ключ для восстановления уже устарел!');

        if ($validation->run()) {

            $newpass    = str_random();
            $hashnewpas = password_hash($newpass, PASSWORD_BCRYPT);

            $user->update([
                'password'   => $hashnewpas,
                'keypasswd'  => null,
                'timepasswd' => 0,
            ]);

            // Восстановление пароля на email
            $subject = 'Восстановление пароля на сайте ' . setting('title');
            $message = 'Здравствуйте, ' . $user['login'] . '<br>Ваши новые данные для входа на на сайт <a href="' . setting('home') . '">' . setting('title') . '</a><br><b>Логин: ' . $user['login'] . '</b><br><b>Пароль: ' . $newpass . '</b><br><br>Запомните и постарайтесь больше не забывать данные <br>Пароль вы сможете поменять в своем профиле<br>Всего наилучшего!';

            $body = view('mailer.default', compact('subject', 'message'), true);
            sendMail($user['email'], $subject, $body);

            return view('mail/restore', ['login' => $user['login'], 'password' => $newpass]);
        } else {
            setFlash('danger', current($validation->getErrors()));
            redirect('/');
        }
    }

    /**
     * Отписка от рассылки
     */
    public function unsubscribe()
    {
        $key = check(Request::input('key'));

        if (! $key) {
            abort('default', 'Отсутствует ключ для отписки от рассылки');
        }

        $user = User::query()->where('subscribe', $key)->first();

        if (! $user) {
            abort('default', 'Ключ для отписки от рассылки устарел!');
        }

        $user->subscribe = null;
        $user->save();

        setFlash('success', 'Вы успешно отписались от рассылки!');
        redirect('/');
    }
}
