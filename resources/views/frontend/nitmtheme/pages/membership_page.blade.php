@extends(theme('layouts.master'))
@section('title')
    {{Settings('site_title')  ? Settings('site_title')  : 'Infix LMS'}} | {{__('membership.Membership')}}
@endsection
@section('css')
    <link href="{{asset('public/frontend/nitmtheme/css/subscription.css')}}{{assetVersion()}}" rel="stylesheet"/>
@endsection


@section('mainContent')

    <x-breadcrumb :banner="$frontendContent->course_page_banner"
                  :title="$frontendContent->course_page_title"
                  :subTitle="$frontendContent->course_page_sub_title"/>
    <x-membership-page-section/>

@endsection
@section('js')
    <script src="{{asset('public/frontend/nitmtheme/js/subscription.js')}}"></script>
@endsection
