@extends('layout')

@section('title')
    {{ $topic['title'] }} (Стр. {{ $page['current'] }}) - @parent
@stop

@section('description', 'Обсуждение темы: '.$topic['title'].' (Стр. '.$page['current'].')')

@section('content')
    <h1>{{ $topic['title'] }}</h1>
    <a href="/forum">Форум</a> /

    @if ($topic->forum->parent)
        <a href="/forum/{{ $topic->forum->parent->id }}">{{ $topic->forum->parent->title }}</a> /
    @endif

    <a href="/forum/{{ $topic->forum->id }}">{{ $topic->forum->title }}</a> /
    <a href="/topic/{{ $topic['id'] }}/print">Печать</a> / <a href="/topic/{{ $topic['id'] }}/rss">RSS-лента</a>

    @if (getUser())
        @if ($topic->user->id == getUser('id') && empty($topic['closed']) && getUser('point') >= setting('editforumpoint'))
           / <a href="/topic/{{ $topic['id'] }}/close?token={{ $_SESSION['token'] }}">Закрыть</a>
           / <a href="/topic/{{ $topic['id'] }}/edit">Изменить</a>
        @endif

        <?php $bookmark = $topic['bookmark_posts'] ? 'Из закладок' : 'В закладки'; ?>
        / <a href="#" onclick="return bookmark(this)" data-tid="{{ $topic['id'] }}" data-token="{{ $_SESSION['token'] }}">{{ $bookmark }}</a>
    @endif

    @if (!empty($topic['curators']))
       <div>
            <span class="label label-info">
                <i class="fa fa-wrench"></i> Кураторы темы:
                @foreach ($topic['curators'] as $key => $curator)
                    <?php $comma = (empty($key)) ? '' : ', '; ?>
                    {{ $comma }}{!! profile($curator) !!}
                @endforeach
            </span>
        </div>
    @endif

    @if (!empty($topic['note']))
        <div class="info">{!! bbCode($topic['note']) !!}</div>
    @endif

    <hr>

    @if (isAdmin())
        @if (empty($topic['closed']))
            <a href="/admin/forum?act=acttopic&amp;do=closed&amp;tid={{ $topic['id'] }}&amp;page={{ $page['current'] }}&amp;token={{ $_SESSION['token'] }}">Закрыть</a> /
        @else
            <a href="/admin/forum?act=acttopic&amp;do=open&amp;tid={{ $topic['id'] }}&amp;page={{ $page['current'] }}&amp;token={{ $_SESSION['token'] }}">Открыть</a> /
        @endif

        @if (empty($topic['locked']))
            <a href="/admin/forum?act=acttopic&amp;do=locked&amp;tid={{ $topic['id'] }}&amp;page={{ $page['current'] }}&amp;token={{ $_SESSION['token'] }}">Закрепить</a> /
        @else
            <a href="/admin/forum?act=acttopic&amp;do=unlocked&amp;tid={{ $topic['id'] }}&amp;page={{ $page['current'] }}&amp;token={{ $_SESSION['token'] }}">Открепить</a> /
        @endif

        <a href="/admin/forum?act=edittopic&amp;tid={{ $topic['id'] }}&amp;page={{ $page['current'] }}">Изменить</a> /
        <a href="/admin/forum?act=movetopic&amp;tid={{ $topic['id'] }}">Переместить</a> /
        <a href="/admin/forum?act=deltopics&amp;fid={{ $topic['forum_id'] }}&amp;del={{ $topic['id'] }}&amp;token={{ $_SESSION['token'] }}" onclick="return confirm('Вы действительно хотите удалить данную тему?')">Удалить</a> /
        <a href="/admin/forum?act=topic&amp;tid={{ $topic['id'] }}&amp;page={{ $page['current'] }}">Управление</a><br>
    @endif

    @if ($vote['answers'])
        <h3>{{ $vote['title'] }}</h3>

        @if (!getUser() || $vote['poll'] || $vote['closed'])
            @foreach ($vote['voted'] as $key => $data)
                <?php $proc = round(($data * 100) / $vote['sum'], 1); ?>
                <?php $maxproc = round(($data * 100) / $vote['max']); ?>

                <b>{{ $key }}</b> (Голосов: {{ $data }})<br>
                {!! progressBar($maxproc, $proc.'%') !!}
            @endforeach
        @else
            <form action="/topic/{{ $topic['id'] }}/vote?page={{ $page['current'] }}" method="post">
                <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
                @foreach ($vote['answers'] as $answer)
                    <label><input name="poll" type="radio" value="{{ $answer['id'] }}"> {{ $answer['answer'] }}</label><br>
                @endforeach
                <br><button class="btn btn-sm btn-primary">Голосовать</button>
            </form><br>
        @endif

        Всего проголосовало: {{ $vote['count'] }}
    @endif

    @if ($topic['isModer'])
        <form action="/topic/{{ $topic['id'] }}/delete?page={{ $page['current'] }}" method="post">
            <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">
        @endif

    @if ($page['total'] > 0)
        @foreach ($posts as $key=>$data)
            <?php $num = ($page['offset'] + $key + 1); ?>
            <div class="post">
            <div class="b" id="post_{{ $data['id'] }}">

                <div class="float-right">
                    @if (getUser('id') != $data['user_id'])
                        <a href="#" onclick="return postReply(this)" title="Ответить"><i class="fa fa-reply text-muted"></i></a>

                        <a href="#" onclick="return postQuote(this)" title="Цитировать"><i class="fa fa-quote-right text-muted"></i></a>

                        <a href="#" onclick="return sendComplaint(this)" data-type="{{ App\Models\Post::class }}" data-id="{{ $data['id'] }}" data-token="{{ $_SESSION['token'] }}" data-page="{{ $page['current'] }}" rel="nofollow" title="Жалоба"><i class="fa fa-bell text-muted"></i></a>
                    @endif

                    @if ((getUser('id') == $data['user_id'] && $data['created_at'] + 600 > SITETIME) || $topic['isModer'])
                        <a href="/topic/{{ $topic['id'] }}/{{ $data['id'] }}/edit?page={{ $page['current'] }}" title="Редактировать"><i class="fa fa-pencil text-muted"></i></a>
                        @if ($topic['isModer'])
                            <input type="checkbox" name="del[]" value="{{ $data['id'] }}">
                        @endif
                    @endif

                    <div class="js-rating">
                        @unless (getUser('id') == $data['user_id'])
                            <a class="post-rating-down{{ $data->vote == -1 ? ' active' : '' }}" href="#" onclick="return changeRating(this);" data-id="{{ $data['id'] }}" data-type="{{ App\Models\Post::class }}" data-vote="-1" data-token="{{ $_SESSION['token'] }}"><i class="fa fa-minus"></i></a>
                        @endunless
                        <span>{!! formatNum($data['rating']) !!}</span>
                        @unless (getUser('id') == $data['user_id'])
                            <a class="post-rating-up{{ $data->vote == 1 ? ' active' : '' }}" href="#" onclick="return changeRating(this);" data-id="{{ $data['id'] }}" data-type="{{ App\Models\Post::class }}" data-vote="1" data-token="{{ $_SESSION['token'] }}"><i class="fa fa-plus"></i></a>
                        @endunless
                    </div>
                </div>

                <div class="img">{!! userAvatar($data->user) !!}</div>

                {{ $num }}. <b>{!! profile($data->user) !!}</b> <small>({{ dateFixed($data['created_at']) }})</small><br>
                {!! userStatus($data->user) !!} {!! userOnline($data->user) !!}
            </div>

            <div class="message">
                {!! bbCode($data['text']) !!}
            </div>

            @if (!$data->files->isEmpty())
                <div class="hiding"><i class="fa fa-paperclip"></i> <b>Прикрепленные файлы:</b><br>
                @foreach ($data->files as $file)
                    <?php $ext = getExtension($file['hash']); ?>

                    {!! icons($ext) !!}
                    <a href="/uploads/forum/{{ $topic['id'] }}/{{ $file['hash'] }}">{{ $file['name'] }}</a> ({{ formatSize($file['size']) }})<br>
                    @if (in_array($ext, ['jpg', 'jpeg', 'gif', 'png']))
                        <a href="/uploads/forum/{{ $topic['id'] }}/{{ $file['hash'] }}" class="gallery" data-group="{{ $data['id'] }}">{!! resizeImage('uploads/forum/', $topic['id'].'/'.$file['hash'], setting('previewsize'), ['alt' => $file['name']]) !!}</a><br>
                    @endif
                @endforeach
                </div>
            @endif

            @if ($data['edit_user_id'])
                <small><i class="fa fa-exclamation-circle text-danger"></i> Отредактировано: {{ $data->editUser->login }} ({{ dateFixed($data['updated_at']) }})</small><br>
            @endif

            @if (isAdmin())
                <span class="data">({{ $data['brow'] }}, {{ $data['ip'] }})</span>
            @endif

            </div>
        @endforeach

    @else
        {{ showError('Сообщений еще нет, будь первым!') }}
    @endif

    @if ($topic['isModer'])
        <span class="float-right">
            <button class="btn btn-danger">Удалить выбранное</button>
        </span>
        </form>
    @endif

    {{ pagination($page) }}

    @if (getUser())
        @if (empty($topic['closed']))
            <div class="form">
                <form action="/topic/{{ $topic['id'] }}/create" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="token" value="{{ $_SESSION['token'] }}">

                    <div class="form-group{{ hasError('msg') }}">
                        <label for="markItUp">Сообщение:</label>
                        <textarea class="form-control" id="markItUp" rows="5" name="msg" placeholder="Текст сообщения" required>{{ getInput('msg') }}</textarea>
                        {!! textError('msg') !!}
                    </div>

                    @if (getUser('point') >= setting('forumloadpoints'))
                        <div class="js-attach-form" style="display: none;">

                            <label class="btn btn-sm btn-secondary" for="inputFile">
                                <input id="inputFile" type="file" name="file"  style="display:none;" onchange="$('#upload-file-info').html($(this).val().replace('C:\\fakepath\\', ''));">
                                Выбрать файл
                            </label>
                            <span class='label label-info' id="upload-file-info"></span>

                            <div class="info">
                                Максимальный вес файла: <b>{{ round(setting('forumloadsize')/1024) }}</b> Kb<br>
                                Допустимые расширения: {{ str_replace(',', ', ', setting('forumextload')) }}
                            </div><br>
                        </div>

                        <span class="imgright js-attach-button">
                            <a href="#" onclick="return showAttachForm();">Загрузить файл</a>
                        </span>
                    @endif

                    <button class="btn btn-primary">Написать</button>
                </form>
            </div><br>

        @else
            {{ showError('Данная тема закрыта для обсуждения!') }}
        @endif
    @else
        {{ showError('Для добавления сообщения необходимо авторизоваться') }}
    @endif

    <a href="/smiles">Смайлы</a>  /
    <a href="/tags">Теги</a>  /
    <a href="/rules">Правила</a> /
    <a href="/forum/top/themes">Топ тем</a> /
    <a href="/forum/top/posts">Топ постов</a> /
    <a href="/forum/search?fid={{ $topic['forum_id'] }}">Поиск</a><br>
@stop
