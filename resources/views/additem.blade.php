@extends('layouts.app')

@section('content')
<form id='additem' method='POST'>
    @csrf

    <label for='content'>content</label>
    <textarea type='text' id='content' rows='3'></textarea>
    <p><small>message body of tweet</small></p>

    <label for='child-type'>child type</label>
    <select id='child-type'>
        <option value='null'>null</option>
        <option value='retweet'>retweet</option>
        <option value='reply'>reply</option>
    </select>
    <p><small>string ("retweet" or "reply"), null if this is not a child tweet</small></p>
    
    <br />
    <button>/additem</button>
</form>
@endsection

@section('style')
<style>
</style>
@endsection

@section('script')
<script>

    /**
     * Handler for /additem form submission
     */
    $('#additem').submit(function() {
        // Get the form data
        var dataArray = $(this).serializeArray();

        // Convert array to an object with the form data
        var dataObj = {};
        for (var i = 0; i < dataArray.length; i++) {
            dataObj[dataArray[i].name] = dataArray[i].value;
        }

        dataObj['content'] = $('#content').val();
        dataObj['childType'] = $('#child-type').find('option:selected').val();
        if (dataObj['childType'] === 'null') {
            dataObj['childType'] = null;
        }

        console.log(JSON.stringify(dataObj, null, 4));

        $.ajax({
            type: 'POST',
            url: '/additem',
            data: JSON.stringify(dataObj),
            contentType: 'application/json',
            dataType: 'json',
            success: function(data) {

                console.log(JSON.stringify(data, null, 4));

                if (data.status === 'OK') {
                    // Clear the fields of the additem form
                    $('#content').val('');
                }

            },
        });

        // Prevent html form from being submitted and the page refreshing
        return false;
    });
</script>
@endsection
