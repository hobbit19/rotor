@extends('layout')

@section('title')
    {{ $find }} - Результаты поиска - @parent
@stop

@section('content')

    <h1>Результаты поиска</h1>

    <h3>Поиск запроса &quot;{{ $find }}&quot; в тексте</h3>
    Найдено совпадений: <b>{{ $page['total'] }}</b><br><br>

    @foreach ($blogs as $data)

        <div class="b">
            <i class="fa fa-pencil"></i>
            <b><a href="/article/{{ $data['id'] }}">{{ $data['title'] }}</a></b> ({!! formatNum($data['rating']) !!})
        </div>

        <?php
        if (utfStrlen($data['text']) > 200):
            $data['text'] = strip_tags(bbCode($data['text']), '<br>');
            $data['text'] = utfSubstr($data['text'], 0, 200).'...';
            endif;
        ?>

        <div>
            {!! $data['text'] !!}<br>

            Категория: <a href="/blog/{{ $data['category_id'] }}">{{ $data['name'] }}</a><br>
            Автор: {!! profile($data['user']) !!}  ({{ dateFixed($data['created_at']) }})
        </div>
    @endforeach

    {{ pagination($page) }}

    <i class="fa fa-arrow-circle-up"></i> <a href="/blog">К блогам</a><br>
    <i class="fa fa-arrow-circle-left"></i> <a href="/blog/search">Вернуться</a><br>
@stop
