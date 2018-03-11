@extends('layouts.app')

@section('content')
<div class='container'>
    <div class='row justify-content-center align-middle'>
        <div class='col-md-6'>
            <div class='card border-secondary'>
                <div class='card-body'>
                    <h2 class="card-title">/additem</h2>

                    <form id='additem' method='POST'>
                        @csrf

                        <div class='form-group'>
                            <label for='content'>content</label>
                            <textarea type='text' class='form-control' id='content' rows='3' required></textarea>
                            <small class="form-text text-muted">message body of tweet</small>
                        </div>

                        <div class='form-group'>
                            <label for='child-type'>child type</label>
                            <select class='form-control' id='child-type'>
                                <option value='null'>null</option>
                                <option value='retweet'>retweet</option>
                                <option value='reply'>reply</option>
                            </select>
                            <small class="form-text text-muted">string ("retweet" or "reply"), null if this is not a child tweet</small>
                        </div>

                        <br />
                        <button class='btn btn-secondary'>/additem</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
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

        console.log(JSON.stringify(dataObj));

        $.ajax({
            type: 'POST',
            url: '/additem',
            data: JSON.stringify(dataObj),
            contentType: 'application/json',
            dataType: 'json',
            success: function(data) {

                console.log(JSON.stringify(data));

                if (data.status === 'OK') {
                    // Clear the fields of the additem form
                    $('#content').val('');

                    $('.card').removeClass('border-secondary border-danger').addClass('border-success');
                } else {
                    $('.card').removeClass('border-success border-secondary').addClass('border-danger');
                }

            },
        });

        // Prevent html form from being submitted and the page refreshing
        return false;
    });
</script>
@endsection
