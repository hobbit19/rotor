<!doctype html>

<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>RotorCMS</title>
    {{ include_style() }}
    {{ include_javascript() }}
</head>
<body>
    {!! App::bbCode($message) !!}
</body>
</html>
