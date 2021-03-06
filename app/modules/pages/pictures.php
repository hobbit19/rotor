<?php

if (! getUser()) {
    abort(403, 'Чтобы загружать фотографии необходимо авторизоваться');
}

switch ($action):
/**
 * Главная страница
 */
case 'index':

    if (Request::isMethod('post')) {

        $newName = uniqid();
        $token   = check(Request::input('token'));

        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], ['photo' => 'Неверный идентификатор сессии, повторите действие!']);

        $handle = uploadImage($_FILES['photo'], setting('filesize'), setting('fileupfoto'), $newName);
        if (! $handle) {
            $validator->addError(['photo' => 'Не удалось загрузить фотографию!']);
        }

        if ($validator->isValid()) {
            //-------- Удаляем старую фотку и аватар ----------//
            $user = User::find(getUser('id'));

            if ($user['picture']) {
                deleteImage('uploads/photos/', $user['picture']);
                deleteImage('uploads/avatars/', $user['avatar']);

                $user->picture = null;
                $user->avatar = null;
                $user->save();
            }

            //-------- Генерируем аватар ----------//
            $handle->process(HOME.'/uploads/photos/');

            if ($handle->processed) {
                $picture = $handle->file_dst_name;

                $handle->file_new_name_body = $newName;
                $handle->image_resize = true;
                $handle->image_ratio_crop = true;
                $handle->image_y = 48;
                $handle->image_x = 48;
                $handle->image_watermark = false;
                $handle->image_convert = 'png';
                $handle->file_overwrite = true;

                $handle->process(HOME . '/uploads/avatars/');
                $avatar = $handle->file_dst_name;

                if ($handle->processed) {

                    $user = User::find(getUser('id'));
                    $user->picture = $picture;
                    $user->avatar = $avatar;
                    $user->save();

                    saveAvatar();
                }

                setFlash('success', 'Фотография успешно загружена!');
                redirect('/profile');
            } else {
                $validator->addError(['photo' => $handle->error]);
            }
        }

        setInput(Request::all());
        setFlash('danger', $validator->getErrors());
    }

    $user = User::where('login', getUser('login'))->first();
    return view('pages/picture', compact('user'));
break;


/**
 * Удаление фото и аватара
 */
case 'delete':

    $token = check(Request::input('token'));

    $validator = new Validator();
    $validator->equal($token, $_SESSION['token'], ['photo' => 'Неверный идентификатор сессии, повторите действие!']);

    $user = User::find(getUser('id'));
    if (! $user || ! $user['picture']) {
        $validator->addError('Фотографии для удаления не существует!');
    }

    if ($validator->isValid()) {

        deleteImage('uploads/photos/', $user['picture']);
        deleteImage('uploads/avatars/', $user['avatar']);

        $user->picture = null;
        $user->avatar = null;
        $user->save();

        setFlash('success', 'Фотография успешно удалена!');
    } else {
        setFlash('danger', $validator->getErrors());
    }

    redirect('/profile');

break;
endswitch;
