@extends(theme('layouts.master'))
@section('title')
    {{Settings('site_title')  ? Settings('site_title')  : 'Infix LMS'}} | {{__('membership.Membership Checkout')}}
@endsection
@section('css')
    <link href="{{asset('public/frontend/nitmtheme/css/select2.min.css')}}{{assetVersion()}}" rel="stylesheet"/>
    <link href="{{asset('public/frontend/nitmtheme/css/checkout.css')}}{{assetVersion()}}" rel="stylesheet"/>
@endsection

@section('mainContent')

    <x-membership-plan-checkout-page-section :request="$request" :plan="$s_plan" :price="$price"/>

@endsection

@section('js')

    <script src="{{asset('public/frontend/nitmtheme/js/select2.min.js')}}{{assetVersion()}}"></script>
    <script src="{{asset('public/frontend/nitmtheme/js/checkout.js')}}{{assetVersion()}}"></script>

@endsection
