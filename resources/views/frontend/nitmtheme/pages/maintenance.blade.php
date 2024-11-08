<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}"/>

    <title>
        {{Settings('site_title')}} | {{$maintain->maintenance_title}}
    </title>


    <x-frontend-dynamic-style-color/>
    <x-backend-dynamic-color/>

    <link rel="stylesheet" href="{{ asset('public/frontend/nitmtheme') }}/css/app.css{{assetVersion()}}">
    <link rel="stylesheet" href="{{ asset('public/frontend/nitmtheme') }}/css/frontend_style.css{{assetVersion()}}">

    <link rel="stylesheet" href="{{asset('public/css/preloader.css')}}{{assetVersion()}}"/>


</head>

<body>

@include('preloader')


<div class="error_wrapper">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-12">
                <div class="error_wrapper_info text-center">
                    <div class="thumb">
                        <img src="{{asset($maintain->maintenance_banner)}}" alt="">
                    </div>
                    <h3>{{$maintain->maintenance_title}}</h3>
                    <p>{!! $maintain->maintenance_sub_title !!}</p>
                </div>
            </div>
        </div>
    </div>
</div>


<script src="{{asset('public/js/common.js')}}"></script>

<script src="{{asset('public/frontend/nitmtheme/js/app.js')}}"></script>


<script>
    setTimeout(function () {
        $('.preloader').fadeOut('slow', function () {
            $(this).remove();
        });
    }, 0);
</script>

</body>

</html>

