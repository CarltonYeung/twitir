@extends('layouts.app')

@section('content')
<form id='adduser' method='POST'>
    @csrf
    <input type='text' name='username' placeholder='Userame'>
    <input type='password' name='password' placeholder='Password'>
    <input type='email' name='email' placeholder='Email'>
    <input type='submit' value='/adduser'>
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

        // console.log(JSON.stringify(dataObj));

        $.ajax({
            type: 'POST',
            url: '/adduser',
            data: JSON.stringify(dataObj),
            contentType: 'application/json',
            dataType: 'json',
            success: function(data) {

                console.log(JSON.stringify(data));

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
