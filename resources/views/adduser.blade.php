@extends('layouts.app')

@section('content')
<form id='adduser' method='POST'>
    @csrf

    <label for='username'>username</label>
    <input type='text' name='username' id='username'>

    <br />
    <label for='password'>password</label>
    <input type='password' name='password' id='password'>
    
    <br />
    <label for='email'>email</label>
    <input type='email' name='email' id='email'>

    <br />
    <button>/adduser</button>
</form>    
@endsection

@section('style')
<style>
</style>
@endsection

@section('script')
<script>

    /**
     * Handler for /adduser form submission
     */
    $('#adduser').submit(function() {
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
            url: '/adduser',
            data: JSON.stringify(dataObj),
            contentType: 'application/json',
            dataType: 'json',
            success: function(data) {

                console.log(JSON.stringify(data, null, 4));

                if (data.status === 'OK') {
                    // Clear the fields of the adduser form
                    $('#adduser input[name=username]').val('');
                    $('#adduser input[name=password]').val('');
                    $('#adduser input[name=email]').val('');
                }

            },
        });

        // Prevent html form from being submitted and the page refreshing
        return false;
    });
</script>
@endsection
