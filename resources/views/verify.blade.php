@extends('layouts.app')

@section('content')
<div class='container'>
    <div class='row justify-content-center align-middle'>
        <div class='col-md-6'>
            <div class='card border-secondary'>
                <div class='card-body'>
                    <h2 class="card-title">/verify</h2>

                    <form id='verify' method='POST'>
                        @csrf

                        <div class='form-group'>
                            <label for='email'>email</label>
                            <input type='email' name='email' id='email' class='form-control' value='{{ $email }}' required>
                        </div>

                        <div class='form-group'>
                            <label for='key'>key</label>
                            <input type='text' name='key' id='key' class='form-control' value='{{ $key }}' required>
                        </div>

                        <br />
                        <button class='btn btn-secondary'>/verify</button>
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

        console.log(JSON.stringify(dataObj, null, 4));

        $.ajax({
            type: 'POST',
            url: '/verify',
            data: JSON.stringify(dataObj),
            contentType: 'application/json',
            dataType: 'json',
            success: function(data) {

                console.log(JSON.stringify(data. null, 4));

                if (data.status === 'OK') {
                    // Clear the fields of the verify form
                    $('#verify input[name=email]').val('');
                    $('#verify input[name=key]').val('');

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
