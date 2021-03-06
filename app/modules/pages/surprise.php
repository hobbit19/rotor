<?php
$surprise['requiredPoint'] = 50;
$surprise['requiredDate'] = '10.01';

$surpriseMoney = mt_rand(10000, 20000);
$surprisePoint = mt_rand(150, 250);
$surpriseRating = mt_rand(3, 7);

$currentYear = date('Y');

if (! getUser()) {
    abort(403, 'Для того чтобы получить сюрприз, необходимо авторизоваться!');
}

if (strtotime(date('d.m.Y')) > strtotime($surprise['requiredDate'].'.'.date('Y'))) {
    abort('default', 'Срок получения сюрприза уже закончился!');
}

if (getUser('point') < $surprise['requiredPoint']) {
    abort('default', 'Для того получения сюрприза необходимо '.plural($surprise['requiredPoint'], setting('scorename')).'!');
}

$existSurprise = Surprise::where('user_id', getUser('id'))
    ->where('year', $currentYear)
    ->first();

if ($existSurprise) {
    abort('default', 'Сюрприз уже получен');
}


$user = User::find(getUser('id'));
$user->update([
    'point' => DB::raw('point + '.$surprisePoint),
    'money' => DB::raw('money + '.$surpriseMoney),
    'rating' => DB::raw('posrating - negrating + '.$surpriseRating),
    'posrating' => DB::raw('posrating + '.$surpriseRating),
]);

$text = 'Поздравляем с новым '.$currentYear.' годом!'.PHP_EOL.'В качестве сюрприза вы получаете '.PHP_EOL.plural($surprisePoint, setting('scorename')).PHP_EOL.plural($surpriseMoney, setting('moneyname')).PHP_EOL.$surpriseRating.' рейтинга репутации'.PHP_EOL.'Ура!!!';

sendPrivate(getUser('login'), env('SITE_ADMIN'), $text);

$surprise = Surprise::create([
    'user_id' => getUser('id'),
    'year' => $currentYear,
    'created_at' => SITETIME,
]);

setFlash('success', 'Сюрприз успешно получен!');
redirect('/');

