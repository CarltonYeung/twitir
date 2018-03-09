<!doctype html>
<html>
    <head>
        <title>@yield('title')</title>

        <!-- https://laravel.com/docs/5.6/csrf -->
        <meta name="csrf-token" content="{{ csrf_token() }}">
    </head>
    <body>
        <h1>twitir</h1>
        @yield('content')
    </body>

    <!-- styles -->
    <!-- Bootstrap -->
    <link rel='stylesheet' type = 'text/css' href='{{ URL::asset('css/app.css') }}'>
    @yield('style')

    <!-- scripts -->
    <script src='https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js'></script>

    <!-- https://laravel.com/docs/5.6/csrf -->
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    @yield('script')
</html>
