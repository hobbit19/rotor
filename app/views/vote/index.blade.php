@extends('layout')

@section('title')
    Голосования - @parent
@stop

@section('content')

    <h1>Голосования</h1>

    @if ($votes->isNotEmpty())
        @foreach ($votes as $vote)
            <div class="b">
                <i class="fa fa-bar-chart"></i>
                <b><a href="/votes/{{ $vote['id'] }}">{{ $vote['title'] }}</a></b>
            </div>
            <div>
                @if ($vote->topic)
                    Тема: <a href="/topic/{{ $vote->getTopic()->id }}">{{ $vote->getTopic()->title }}</a><br>
                @endif

                Создано: {{ date_fixed($vote['created_at']) }}<br>
                Всего голосов: {{ $vote['count'] }}<br>
            </div>
        @endforeach

        <br>
    @else
        {{ show_error('Открытых голосований еще нет!') }}
    @endif
@stop