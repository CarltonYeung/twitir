@extends('layouts.app')

@section('title', 'twitir')

@section('content')
<form id='adduser' method='POST'>
	@csrf
  <input type='text' name='username' placeholder='Name'>
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
	$('#adduser').submit(function() {
		// Get the form data
		var dataArray = $(this).serializeArray();

		// Convert array to object with the form data
		var dataObj = {};
		for (var i = 0; i < dataArray.length; i++) {
			dataObj[dataArray[i].name] = dataArray[i].value;
		}

		console.log(JSON.stringify(dataObj));

		// Prevent html form from being submitted and the page refreshing
		return false;
	});
</script>
@endsection
