@extends(theme('layouts.master'))
@section('title')
    {{Settings('site_title')  ? Settings('site_title')  : 'Infix LMS'}} | {{__('common.Checkout')}}
@endsection
@section('css')
    <link href="{{asset('public/frontend/nitmtheme/css/select2.min.css')}}{{assetVersion()}}" rel="stylesheet"/>
@endsection
@section('mainContent')

    <x-checkout-page-section :request="$request"/>

@endsection
@section('js')
    <script src="{{asset('public/frontend/nitmtheme/js/select2.min.js')}}"></script>
    <script src="{{asset('public/frontend/nitmtheme/js/checkout.js')}}"></script>
    <script src="{{asset('public/frontend/nitmtheme/js/city.js')}}"></script>
@endsection
