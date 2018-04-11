@extends('layouts.app')

@section('content')
<form id='addmedia' method='POST' action='/addmedia'>
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
</script>
@endsection
