@extends('layouts.app')

@section('content')
<form id='addmedia' method='POST' enctype='multipart/form-data'>
    @csrf

    <label for='contents'>media</label>
    <input type='file' name='contents' id='contents' accept='image/*|video/*'>
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
            data: new FormData($('#addmedia')),
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
