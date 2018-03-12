@extends('layouts.app')

@section('content')
<div class='container'>
    <div class='row justify-content-center align-middle'>
        <div class='col-md-6'>
            <div class='card border-secondary'>
                <div class='card-body'>
                    <h2 class="card-title">/search</h2>

                    <form id='search' method='POST'>
                        @csrf

                        <div class='form-group'>
                            <label for='timestamp'>timestamp (optional)</label>
                            <input type='number' id='timestamp' class='form-control'>
                            <small class="form-text text-muted">leave blank to use the current time</small>
                            <small class="form-text text-muted">search tweets from this time and earlier</small>
                            <small class="form-text text-muted">represented as unix time in seconds</small>
                        </div>

                        <div class='form-group'>
                            <label for='limit'>limit (optional)</label>
                            <input type='number' id='limit' class='form-control'>
                            <small class="form-text text-muted">leave blank for default: 25</small>
                            <small class="form-text text-muted">number of tweets to return</small>
                            <small class="form-text text-muted">max: 100</small>
                        </div>

                        <br />
                        <button class='btn btn-secondary'>/search</button>
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
