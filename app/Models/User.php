<?php

namespace App\Models;

use Curl\Curl;
use Illuminate\Database\Capsule\Manager as DB;

class User extends BaseModel
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Авторизует пользователя
     *
     * @param  string  $login    Логин
     * @param  string  $password Пароль пользователя
     * @param  boolean $remember Запомнить пароль
     * @return User|boolean      Результат авторизации
     */
    public static function login($login, $password, $remember = true)
    {
        $domain = siteDomain(setting('home'));

        if (!empty($login) && !empty($password)) {

            $user = self::whereRaw('LOWER(login) = ?', [$login])->first();

            /* Миграция старых паролей */
            if (preg_match('/^[a-f0-9]{32}$/', $user['password']))
            {
                if (md5(md5($password)) == $user['password']) {
                    $user['password'] = password_hash($password, PASSWORD_BCRYPT);

                    $user = self::where('login', $user['login'])->first();
                    $user->password = $user['password'];
                    $user->save();
                }
            }

            if ($user && password_verify($password, $user['password'])) {

                if ($remember) {
                    setcookie('login', $user['login'], SITETIME + 3600 * 24 * 365, '/', $domain);
                    setcookie('password', md5($user['password'].env('APP_KEY')), SITETIME + 3600 * 24 * 365, '/', $domain, null, true);
                }

                $_SESSION['id']       = $user->id;
                $_SESSION['password'] = md5(env('APP_KEY').$user->password);

                // Сохранение привязки к соц. сетям
                if (! empty($_SESSION['social'])) {
                    Social::create([
                        'user_id' => $user->id,
                        'network' => $_SESSION['social']->network,
                        'uid'     => $_SESSION['social']->uid,
                    ]);
                }

                $authorization = Login::where('user_id', $user->id)
                    ->where('created_at', '>', SITETIME - 30)
                    ->first();

                if (! $authorization) {

                    Login::create([
                        'user_id' => $user->id,
                        'ip' => getClientIp(),
                        'brow' => getUserAgent(),
                        'created_at' => SITETIME,
                        'type' => 1,
                    ]);

                    DB::delete('
                        DELETE FROM login WHERE created_at < (
                            SELECT MIN(created_at) FROM (
                                SELECT created_at FROM guest ORDER BY created_at DESC LIMIT 50
                            ) AS del
                        );'
                    );
                }

                $user->update([
                    'visits' => DB::raw('visits + 1'),
                    'timelastlogin' => SITETIME
                ]);

                return $user;
            }
        }

        return false;
    }

    /**
     * Авторизует через социальные сети
     *
     * @param string $token идентификатор Ulogin
     */
    public static function socialLogin($token)
    {
        $domain = siteDomain(setting('home'));

        $curl = new Curl();
        $network = $curl->get('http://ulogin.ru/token.php',
            [
                'token' => $token,
                'host' => $_SERVER['HTTP_HOST']
            ]
        );

        if ($network && empty($network->error)) {
            $_SESSION['social'] = $network;

            $social = Social::where('network', $network->network)
                ->where('uid', $network->uid)
                ->first();

            if ($social && $user = getUserById($social->user_id)) {

                setcookie('login', $user->login, SITETIME + 3600 * 24 * 365, '/', $domain);
                setcookie('password', md5($user->password.env('APP_KEY')), SITETIME + 3600 * 24 * 365, '/', $domain, null, true);

                $_SESSION['id']       = $user->id;
                $_SESSION['password'] = md5(env('APP_KEY').$user->password);

                setFlash('success', 'Добро пожаловать, '.$user->login.'!');
                redirect('/');
            }
        }
    }
}