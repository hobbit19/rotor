<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Rating;
use App\Models\User;

class RatingController extends BaseController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! getUser()) {
            abort(403, 'Для просмотра истории небходимо авторизоваться!');
        }
    }

    /**
     *  Полученные голоса
     */
    public function received($login)
    {
        $user = User::query()->where('login', $login)->first();

        if (! $user) {
            abort('default', 'Данного пользователя не существует!');
        }

        $ratings = Rating::query()
            ->where('recipient_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->with('user')
            ->get();

        return view('pages/rathistory', compact('ratings', 'user'));
    }

    /**
     *  Отданные голоса
     */
    public function gave($login)
    {
        $user = User::query()->where('login', $login)->first();

        if (! $user) {
            abort('default', 'Данного пользователя не существует!');
        }

        $ratings = Rating::query()
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->with('recipient')
            ->get();

        return view('pages/rathistory_gave', compact('ratings', 'user'));
    }

    /**
     *  Удаление истории
     */
    public function delete()
    {
        $id    = abs(intval(Request::input('id')));
        $token = check(Request::input('token'));

        $validator = new Validator();
        $validator
            ->true(Request::ajax(), 'Это не ajax запрос!')
            ->true(isAdmin(User::ADMIN), 'Удалять рейтинг могут только администраторы')
            ->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->notEmpty($id, ['Не выбрана запись для удаление!']);

        if ($validator->isValid()) {

            Rating::query()->find($id)->delete();

            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => current($validator->getErrors())
            ]);
        }
    }
}
