@extends('layouts.app')

@section('content')
<form id='addmedia' method='POST'>
    @csrf

    <label for='media'>media</label>
    <input type='file' name='media' id='media' accept='image/*|video/*'>
    
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
    // $('#addmedia').submit(function() {
    //     var dataArray = $(this).serializeArray();

    //     var dataObj = {};
    //     for (var i = 0; i < dataArray.length; i++) {
    //         dataObj[dataArray[i].name] = dataArray[i].value;
    //     }

    //     dataObj['content'] = $('#content').val();
    //     dataObj['childType'] = $('#child-type').find('option:selected').val();
    //     if (dataObj['childType'] === 'null') {
    //         dataObj['childType'] = null;
    //     }

    //     console.log(JSON.stringify(dataObj, null, 4));

    //     $.ajax({
    //         type: 'POST',
    //         url: '/additem',
    //         data: JSON.stringify(dataObj),
    //         contentType: 'application/json',
    //         dataType: 'json',
    //         success: function(data) {

    //             console.log(JSON.stringify(data, null, 4));

    //             if (data.status === 'OK') {
    //                 // Clear the fields of the additem form
    //                 $('#content').val('');
    //             }

    //         },
    //     });

    //     // Prevent html form from being submitted and the page refreshing
    //     return false;
    // });
</script>
@endsection
