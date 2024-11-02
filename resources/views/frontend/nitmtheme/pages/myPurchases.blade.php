@extends(theme('layouts.dashboard_master'))
@section('title')
    {{Settings('site_title')  ? Settings('site_title')  : 'Infix LMS'}} | {{__('payment.Payment History')}}
@endsection
@section('css')
    <style>
        .thumb img {
            height: 80px;
        }
    </style>
@endsection

@section('js') @endsection

@section('mainContent')
    <x-my-purchase-page-secton/>
@endsection
