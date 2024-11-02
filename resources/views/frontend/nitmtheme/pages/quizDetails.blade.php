@extends(theme('layouts.master'))
@section('title')
    {{Settings('site_title')  ? Settings('site_title')  : 'Infix LMS'}} |  {{$course->title}}
@endsection

@section('css')
    <style>
        iframe {
            position: relative !important;
        }
    </style>
    {{-- <link rel="stylesheet" href="{{asset('public/frontend/nitmtheme/css/class_details.css')}}{{assetVersion()}}"/> --}}
    {{-- <link rel="stylesheet" href="{{asset('public/frontend/nitmtheme/css/quiz_details.css')}}{{assetVersion()}}"/> --}}
    @if(isModuleActive('WaitList'))
        <link href="{{asset('public/frontend/nitmtheme/css/select2.min.css')}}{{assetVersion()}}" rel="stylesheet"/>
    @endif
@endsection
@section('meta_title')
    {{$course->meta_keywords}}
@endsection
@section('meta_description')
    {{$course->meta_description}}
@endsection
@section('og_image')
    {{getCourseImage($course->image)}}
@endsection
@section('mainContent')

    <x-breadcrumb :banner="$frontendContent->quiz_page_banner"
                  :title="trans('frontend.Quiz Details')"
                  :subTitle="$course->title"/>

    <x-quiz-details-page-section :course="$course" :request="$request" :isEnrolled="$isEnrolled"/>

@endsection

@section('js')
    <script src="{{ asset('public/frontend/nitmtheme') }}/js/html2pdf.bundle.js{{assetVersion()}}"></script>
    <script src="{{ asset('public/frontend/nitmtheme/js/quiz_details.js') }}{{assetVersion()}}"></script>
    <script src="{{asset('public/frontend/nitmtheme/js/class_details.js')}}{{assetVersion()}}"></script>
    @if(isModuleActive('WaitList'))
        <script src="{{asset('public/frontend/nitmtheme/js/city.js')}}{{assetVersion()}}"></script>
        <script src="{{asset('public/frontend/nitmtheme/js/select2.min.js')}}{{assetVersion()}}"></script>
    @endif
    <script>

        $("#formSubmitBtn").on("click", function (e) {
            e.preventDefault();

            var form = $('#deleteCommentForm');
            var url = form.attr('action');
            var element = form.data('element');
            $.ajax({
                type: "POST",
                url: url,
                data: form.serialize(),
                success: function (data) {

                }
            });
            var el = '#' + element;
            $(el).hide('slow');
            $('#deleteComment').modal('hide');

        });
    </script>

    <script>
        function deleteCommnet(item, element) {
            let form = $('#deleteCommentForm')
            form.attr('action', item);
            form.attr('data-element', element);
        }
    </script>


    <script>

        var SITEURL = "{{courseDetailsUrl($course->id,$course->type,$course->slug)}}";
        var page = 1;
        load_more(page);
        $(window).scroll(function () { //detect page scroll
            if ($(window).scrollTop() + $(window).height() >= $(document).height() - 400) {
                page++;
                load_more(page);
            }


        });

        function load_more(page) {
            $.ajax({
                url: SITEURL + "?page=" + page,
                type: "get",
                datatype: "html",
                data: {'type': 'comment'},
                beforeSend: function () {
                    $('.ajax-loading').show();
                }
            })
                .done(function (data) {
                    if (data.length == 0) {

                        //notify user if nothing to load
                        $('.ajax-loading').html("");
                        return;
                    }
                    $('.ajax-loading').hide(); //hide loading animation once data is received
                    $("#conversition_box").append(data); //append data into #results element


                })
                .fail(function (jqXHR, ajaxOptions, thrownError) {
                    console.log('No response from server');
                });

        }


        load_more_review(page);


        $(window).scroll(function () { //detect page scroll
            if ($(window).scrollTop() + $(window).height() >= $(document).height() - 400) {
                page++;
                load_more_review(page);
            }


        });

        function load_more_review(page) {
            $.ajax({
                url: SITEURL + "?page=" + page,
                type: "get",
                datatype: "html",
                data: {'type': 'review'},
                beforeSend: function () {
                    $('.ajax-loading').show();
                }
            })
                .done(function (data) {
                    if (data.length == 0) {

                        //notify user if nothing to load
                        $('.ajax-loading').html("");
                        return;
                    }
                    $('.ajax-loading').hide(); //hide loading animation once data is received
                    $("#customers_reviews").append(data); //append data into #results element


                })
                .fail(function (jqXHR, ajaxOptions, thrownError) {
                    console.log('No response from server');
                });

        }
    </script>
@endsection
