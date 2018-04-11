@extends('layouts.app')

@section('content')
<form enctype='multipart/form-data'>
    @csrf

    <input type='file' name='content' accept='image/*|video/*'>
    <br />
    <br />
    <button>/addmedia</button>
</form>
@endsection

@section('style')
<style>
</style>
@endsection

@section('script')
<script>
    $('#addmedia').submit(function() {
        $.ajax({
            type: 'POST',
            url: '/addmedia',
            data: new FormData($('form')[0]),
            cache: false,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(data) {

                console.log(JSON.stringify(data, null, 4));

            },
        });

        // Prevent html form from being submitted and the page refreshing
        return false;
    });
</script>
@endsection
