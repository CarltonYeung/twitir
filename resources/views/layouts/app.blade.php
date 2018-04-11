<!DOCTYPE html>

<head>
    <title>{{ config('app.name') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @yield('style')
</head>
<body>
    <ul>
        <li><a href="{{ route('home') }}">home</a></li>
        <li><a href="{{ route('search') }}">/search</a></li>
        @guest
            <li><a href="{{ route('verify') }}">/verify</a></li>
            <li><a href="{{ route('login') }}">/login</a></li>
            <li><a href="{{ route('adduser') }}">/adduser</a></li>
        @else
            <li><a href="{{ route('additem') }}">/additem</a></li>
            <li><a href="{{ route('addmedia') }}">/addmedia</a></li>
            <li><a href="{{ route('logout') }}">/logout</a></li>
        @endguest
    </ul>

    @yield('content')

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#logout-form').submit(function() {
            // Get the form data
            var dataArray = $(this).serializeArray();

            // Convert array to an object with the form data
            var dataObj = {};
            for (var i = 0; i < dataArray.length; i++) {
                dataObj[dataArray[i].name] = dataArray[i].value;
            }

            console.log(JSON.stringify(dataObj, null, 4));

            $.ajax({
                type: 'POST',
                url: '/logout',
                data: JSON.stringify(dataObj),
                contentType: 'application/json',
                dataType: 'json',
                success: function(data) {

                    console.log(JSON.stringify(data, null, 4));

                },
            });
        });
    </script>
    @yield('script')
</body>
</html>
