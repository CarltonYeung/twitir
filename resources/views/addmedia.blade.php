@extends('layouts.app')

@section('content')
<form id='addmedia' method = 'POST' enctype='multipart/form-data'>
    @csrf

    <input type='file' name='content' id='content' accept='image/*|video/*'>
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
        var fd = new FormData();
        fd.append('content');

        $.ajax({
            type: 'POST',
            url: '/addmedia',
            data: fd,
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
