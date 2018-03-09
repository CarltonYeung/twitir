<!doctype html>
<html>
    <head>
        <title>@yield('title')</title>

        <!-- Without this line, POST will produce HTTP 419 error -->
        <meta name="csrf-token" content="{{ csrf_token() }}">
    </head>
    <body>
        <div id='left-center-right-grid'>
            <div id='left'>
                @yield('left')
            </div>
            <div id='center'>
                @yield('center')
            </div>
            <div id='right'>
                @yield('right')
            </div>
        </div>
    </body>

    <!-- STYLES -->
    <!-- Bootstrap -->
    <link rel='stylesheet' type = 'text/css' href='{{ URL::asset('css/app.css') }}'>
    <link rel='stylesheet' type = 'text/css' href='{{ URL::asset('css/main.css') }}'>
    @yield('page-style')

    <!-- SCRIPTS -->
    <!-- jQuery -->
    <script src='https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js'></script>
    @yield('page-script')
</html>
