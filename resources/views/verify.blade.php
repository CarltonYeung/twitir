@extends('layouts.app')

@section('content')
<form id='verify' method='POST'>
    @csrf
    <input type='email' name='email' placeholder='email' value='{{ $email }}'>
    <input type='text' name='key' placeholder='key' value='{{ $key }}'>
    <input type='submit' value='/verify'>
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

        // console.log(JSON.stringify(dataObj));

        $.ajax({
            type: 'POST',
            url: '/verify',
            data: JSON.stringify(dataObj),
            contentType: 'application/json',
            dataType: 'json',
            success: function(data) {

                console.log(JSON.stringify(data));

                if (data.status === 'OK') {
                    // Clear the fields of the verify form
                    $('#verify input[name=email]').val('');
                    $('#verify input[name=key]').val('');
                }

            },
        });

        // Prevent html form from being submitted and the page refreshing
        return false;
    });
</script>
@endsection
