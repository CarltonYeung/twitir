@extends('layouts.app')

@section('content')

@guest
    <form id='login' method='POST'>
        @csrf

        <label for='username'>username</label>
        <input type='text' name='username' id='username'>
        
        <br />
        <label for='password'>password</label>
        <input type='password' name='password' id='password'>

        <br />
        <button>/login</button>
    </form>
@else
    <p>you're awesome! thanks for logging in!<p>
@endguest
@endsection

@section('style')
<style>
</style>
@endsection

@section('script')
<script>

    /**
     * Handler for /login form submission
     */
    $('#login').submit(function() {
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
            url: '/login',
            data: JSON.stringify(dataObj),
            contentType: 'application/json',
            dataType: 'json',
            success: function(data) {

                console.log(JSON.stringify(data, null, 4));

                if (data.status === 'OK') {
                    // Clear the fields of the verify form
                    $('#username').val('');
                    $('#password').val('');
                }

            },
        });

        // Prevent html form from being submitted and the page refreshing
        return false;
    });
</script>
@endsection
