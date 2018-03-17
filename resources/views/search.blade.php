@extends('layouts.app')

@section('content')
<form id='search' method='POST'>
    @csrf

    <label for='timestamp'>timestamp (optional)</label>
    <input type='number' id='timestamp'>
    <p><small>leave blank to use the current time</small></p>
    <p><small>search tweets from this time and earlier</small></p>
    <p><small>represented as unix time in seconds</small></p>

    <br />
    <label for='limit'>limit (optional)</label>
    <input type='number' id='limit'>
    <p><small>leave blank for default: 25</small></p>
    <p><small>number of tweets to return</small></p>
    <p><small>max: 100</small></p>

    <br />
    <button>/search</button>
</form>          
@endsection

@section('style')
<style>
</style>
@endsection

@section('script')
<script>

    /**
     * Handler for /search form submission
     */
    $('#search').submit(function() {
        var dataArray = $(this).serializeArray();
        var dataObj = {};
        for (var i = 0; i < dataArray.length; i++) {
            dataObj[dataArray[i].name] = dataArray[i].value;
        }

        if ($('#timestamp').val()) {
            dataObj['timestamp'] = Number($('#timestamp').val());
        }

        if ($('#limit').val()) {
            dataObj['limit'] = Number($('#limit').val());
        }

        console.log(JSON.stringify(dataObj, null, 4));

        $.ajax({
            type: 'POST',
            url: '/search',
            data: JSON.stringify(dataObj),
            contentType: 'application/json',
            dataType: 'json',
            success: function(data) {

                console.log(JSON.stringify(data, null, 4));

                if (data.status === 'OK') {

                    $('#timestamp').val('');
                    $('#limit').val('');
                }

            },
        });

        // Prevent html form from being submitted and the page refreshing
        return false;
    });
</script>
@endsection
