@extends('layouts.app')

@section('content')
<form id='verify' method='POST'>
    @csrf

    <label for='email'>email</label>
    <input type='email' name='email' id='email' value='{{ $email }}'>
    
    <br />
    <label for='key'>key</label>
    <input type='text' name='key' id='key' value='{{ $key }}'>

    <br />
    <button>/verify</button>
</form>
@endsection

@section('style')
<style>
</style>
@endsection

@section('script')
<script>

    /**
     * Handler for /verify form submission
     */
    $('#verify').submit(function() {
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
            url: '/verify',
            data: JSON.stringify(dataObj),
            contentType: 'application/json',
            dataType: 'json',
            success: function(data) {

                console.log(JSON.stringify(data, null, 4));

                if (data.status === 'OK') {
                    // Clear the fields of the verify form
                    $('#email').val('');
                    $('#key').val('');
                }

            },
        });

        // Prevent html form from being submitted and the page refreshing
        return false;
    });
</script>
@endsection
