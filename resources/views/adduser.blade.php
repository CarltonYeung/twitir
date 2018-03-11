@extends('layouts.app')

@section('content')
<div class='container'>
    <div class='row justify-content-center align-middle'>
        <div class='col-md-6'>
            <div class='card border-secondary'>
                <div class='card-body'>
                    <h2 class="card-title">/adduser</h2>

                    <form id='adduser' method='POST'>
                        @csrf

                        <div class='form-group'>
                            <label for='username'>username</label>
                            <input type='text' name='username' id='username' class='form-control' required>
                        </div>

                        <div class='form-group'>
                            <label for='password'>password</label>
                            <input type='password' name='password' id='password' class='form-control'  required>
                        </div>

                        <div class='form-group'>
                            <label for='email'>email</label>
                            <input type='email' name='email' id='email' class='form-control'  required>
                        </div>

                        <br />
                        <button class='btn btn-secondary'>/adduser</button>
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
     * Handler for /adduser form submission
     */
    $('#adduser').submit(function() {
        // Get the form data
        var dataArray = $(this).serializeArray();

        // Convert array to an object with the form data
        var dataObj = {};
        for (var i = 0; i < dataArray.length; i++) {
            dataObj[dataArray[i].name] = dataArray[i].value;
        }

        // console.log(JSON.stringify(dataObj));

        $.ajax({
            type: 'POST',
            url: '/adduser',
            data: JSON.stringify(dataObj),
            contentType: 'application/json',
            dataType: 'json',
            success: function(data) {

                console.log(JSON.stringify(data));

                if (data.status === 'OK') {
                    // Clear the fields of the adduser form
                    $('#adduser input[name=username]').val('');
                    $('#adduser input[name=password]').val('');
                    $('#adduser input[name=email]').val('');

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
