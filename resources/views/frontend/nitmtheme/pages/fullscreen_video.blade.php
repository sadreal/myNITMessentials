@extends(theme('layouts.full_screen_master'))
@section('title')
    {{Settings('site_title')  ? Settings('site_title')  : 'Infix LMS'}} | {{ $course->title}}
@endsection
@section('css')
    @if(isRtl())
        <link href="{{asset('public/frontend/nitmtheme/css/full_screen_rtl.css')}}{{assetVersion()}}"
              rel="stylesheet"/>
    @else
        <link href="{{asset('public/frontend/nitmtheme/css/full_screen.css')}}{{assetVersion()}}" rel="stylesheet"/>
    @endif
    {{-- <link href="{{asset('public/frontend/nitmtheme/css/class_details.css')}}{{assetVersion()}}" rel="stylesheet"/> --}}
    <link href="{{asset('public/backend/css/summernote-bs4.min.css')}}{{assetVersion()}}" rel="stylesheet">
    <style>
        .default-font {
            font-family: "Jost", sans-serif;
            font-weight: normal;
            font-style: normal;
            font-weight: 400;
        }

        .primary_checkbox {
            z-index: 99;
        }

        @media (max-width: 767.98px) {
            .contact_btn {
                margin: 0 !important;
                justify-content: space-between;
            }

            #video-placeholder {
                height: 300px;
            }
        }

        .course__play_warp.courseListPlayer:before {
            background-color: transparent;
        }

        @media (max-width: 991.98px) {
            .mobile-min-height {
                height: 330px !important;
            }
        }

        #ExternalHeaderViewerChromeTopBars {
            display: none !important;
        }

        .quiz_questions_wrapper {
            height: 100%;
        }

        .question_number_lists {
            max-height: 320px;
            overflow: auto;
        }

        .logo_img {
            height: 50px !important;
        }

        @media (max-width: 991.98px) {
            .header_area .header__wrapper .header__left .logo_img img {
                padding: .5rem !important
            }
        }

        .inline-YTPlayer {
            height: auto !important;
        }

        .quiz_score_wrapper .quiz_test_body .score_view_wrapper {
            justify-content: space-around;
        }

        html[dir=rtl] .fa-angle-left,
        html[dir=rtl] .fa-angle-right {
            transform: scaleX(-1)
        }

        @media (max-width: 991px) {
            .course_fullview_wrapper .video_iframe {
                position: initial !important;
                height: 400px;
                width: 100%;
            }
        }

        @media (min-width: 576px) {
            .modal-dialog {
                max-width: 550px;
            }
        }

        @media (min-width: 1080px) {
            .modal-dialog {
                max-width: 800px;
            }
        }

        .conversition_box .single_comment_box .comment_box_inner .comment_box_info .comment_box_text span {
            font-size: 14px;
            font-weight: 400;
            margin-bottom: 10px;
            margin-top: 2px;
            display: block;
            color: #7b7887;
        }

    </style>
@endsection

@section('mainContent')
    @php
        $video_lesson_hosts=['Iframe','Image','PDF','Word','Excel','PowerPoint','Text','Zip','GoogleDrive','H5P'];
    @endphp
    @push('js')
        <script>
            $(document).on('click', '.showHistory', function (e) {
                e.preventDefault();
                $("#historyDiv").toggle('slow')
            });
        </script>
        <script>
            var completeRequest = false;
        </script>
    @endpush

    @php
        if ($lesson->lessonQuiz->random_question==1){
        $questions =$lesson->lessonQuiz->assignRand;
        }else{
        $questions =$lesson->lessonQuiz->assign;
       }
    @endphp

    <script>
        @if(auth()->check())
            window.full_name = "{{auth()->user()->name}}";
        window.course_name = "{{ $course->title}}";
        @if(isModuleActive('Org'))
            window.org_chart_name = "{{auth()->user()->branch->group}}";
        @endif
            @else
            window.full_name = "Guest";
        window.course_name = "{{ $course->title}}";
        @if(isModuleActive('Org'))
            window.org_chart_name = "";
        @endif
        @endif
    </script>
    <header>
        <div id="sticky-header" class="header_area">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="header__wrapper flex-wrap">
                            <div class="header__left d-flex align-items-center">
                                <div class="">
                                    <a class="logo_img" href="{{url('/')}}">
                                        <img class="p-2" src="{{getLogoImage(Settings('logo') )}}" width="150"
                                             alt="{{ Settings('site_name')  }}">
                                    </a>
                                </div>
                                <div class="category_search d-none d-lg-flex category_box_iner">
                                    <div class="input-group-prepend2 ps-3 ">
                                        <a class="headerTitle"
                                           href="{{ courseDetailsUrl($course->id, $course->type, $course->slug) }}">
                                            <h4 class="headerTitle">{{ $course->title }}</h4>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="header__right">
                                <div class="contact_wrap d-flex align-items-center flex-wrap mx-0">
                                    <div class="contact_btn d-flex align-items-center flex-wrap">
                                        @if (in_array($lesson->host, $video_lesson_hosts))
                                            <button
                                                class="theme_btn small_btn2 p-2 me-2 mr-lg-4 fs-14 completeAndPlayNext">
                                                {{ __('frontend.Mark as Complete') }}</button>
                                        @endif
                                        @if (isset($lessons))
                                            <div class="d-flex aling-items-center">
                                                <label class="lmsSwitch_toggle pe-2" for="autoNext">
                                                    <input type="checkbox" id="autoNext" checked>
                                                    <div class="slider round"></div>
                                                </label>
                                                <span class="ps-2 text-nowrap">{{ __('frontend.Auto Next') }}</span>
                                            </div>
                                            <div class="pl-20 text-end ms-3 d-flex align-items-center">
                                                @php
                                                    $last_key = array_key_last($lesson_ids);
                                                    $last_previous_one = array_key_last($lesson_ids) - 1;
                                                    $current_page = (int) showPicName(Request::url());

                                                    $current_index = array_search(showPicName(Request::url()), $lesson_ids);
                                                @endphp
                                                @if (0 == array_search($current_page, $lesson_ids))
                                                    <a href="#" disabled="disabled"
                                                       class="header__common_btn theme_button_disabled disabled">
                                                        <i class="fa fa-angle-left"></i>
                                                    </a>
                                                @else
                                                    <a href="#"
                                                       onclick="goFullScreen({{ $course->id }},{{ $lesson_ids[$current_index - 1] }})"
                                                       class="header__common_btn"><i class="fa fa-angle-left"></i>
                                                    </a>
                                                @endif
                                                @if (array_search($current_page, $lesson_ids) < array_search(end($lesson_ids), $lesson_ids))
                                                    <a href="#" id="next_lesson_btn"
                                                       onclick="goFullScreen({{ $course->id }},{{ $lesson_ids[$current_index + 1] }})"
                                                       class="header__common_btn ms-2">
                                                        <i class="fa fa-angle-right"></i>
                                                    </a>
                                                @else
                                                    <a href="#" disabled
                                                       class="header__common_btn theme_button_disabled ms-2 disabled"
                                                       style="opacity: 1">
                                                        <i class="fa fa-angle-right"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        @endif
                                        <a href="javascript:void(0)" class="ms-2 mobile_progress">
                                            <div class="progress p-2" data-percentage="{{ $percentage }}">
                                                <span class="progress-left">
                                                    <span class="progress-bar"></span>
                                                </span>
                                                <span class="progress-right">
                                                    <span class="progress-bar"></span>
                                                </span>
                                                <div class="progress-value">
                                                    <div class="headerSubProcess">
                                                        {{ $percentage }}%
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                        <div class="header__common_btn dropdown ms-2">
                                            <button
                                                class="d-block w-100 h-100 bg-transparent border-0 dropdown-toggle outline-none border-0 p-0 currentColor"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fa fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#" data-bs-toggle="modal"
                                                       data-bs-target="#ShareLink"><i
                                                            class="fa fa-share fs-12 me-2"></i>{{ __('frontend.Share') }}
                                                    </a></li>
                                                @if (Auth::check())
                                                    @if (Auth::user()->role_id == 3)
                                                        @if (!in_array(Auth::user()->id, $reviewer_user_ids))
                                                            <li>
                                                                <a href="#" data-bs-toggle="modal"
                                                                   data-bs-target="#courseRating" class="dropdown-item">
                                                                    <i class="fa fa-star me-2 fs-12"></i>{{ __('frontend.Leave a rating') }}
                                                                </a>
                                                            </li>
                                                        @endif
                                                    @endif
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="course_fullview_wrapper">
        @if ($lesson->is_quiz == 1)
            @if (count($result) != 0)
                <div class="quiz_score_wrapper w-100 mt_70">
                    @if (!isset($_GET['done']))
                        <div class="quiz_test_header">
                            <h3>{{ __('student.Your Exam Score') }}</h3>
                        </div>
                        <div class="quiz_test_body">
                            <h3>{{ __('student.Congratulations! You’ve completed') }} {{ $course->quiz->title }}</h3>
                            @if ($result['publish'] == 1)
                                <div class="">
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <div class="score_view_wrapper">
                                                <div class="single_score_view">
                                                    <p>{{ __('student.Exam Score') }}:</p>
                                                    <ul>
                                                        <li class="mb_15">
                                                            <label class="primary_checkbox2 d-flex">
                                                                <input checked="" type="checkbox" disabled>
                                                                <span class="checkmark mr_10"></span>
                                                                <span class="label_name">{{ $result['totalCorrect'] }}
                                                                    {{ __('student.Correct Answer') }}</span>
                                                            </label>
                                                        </li>
                                                        <li>
                                                            <label class="primary_checkbox2 error_ans d-flex">
                                                                <input checked="" name="qus" type="checkbox"
                                                                       disabled>
                                                                <span class="checkmark mr_10"></span>
                                                                <span class="label_name">{{ $result['totalWrong'] }}
                                                                    {{ __('student.Wrong Answer') }}</span>
                                                            </label>
                                                        </li>
                                                    </ul>
                                                </div>

                                                <div class="single_score_view d-flex">
                                                    <div class="row">
                                                        <div class="col md-2">
                                                            <p>{{ __('frontend.Start') }}</p>
                                                            <span> {{ $result['start_at'] }} </span>
                                                        </div>
                                                        <div class="col md-2">
                                                            <p>{{ __('frontend.Finish') }}</p>
                                                            <span> {{ $result['end_at'] }} </span>
                                                        </div>
                                                        <div class="col md-2">
                                                            <p class="nowrap">{{ __('frontend.Duration') }}
                                                                ({{ __('frontend.Minute') }})</p>
                                                            <h4 class="f_w_700 "> {{ $result['duration'] }} </h4>
                                                        </div>
                                                        <div class="col md-2">
                                                            <p>{{ __('frontend.Mark') }}</p>
                                                            <h4 class="f_w_700 "> {{ $result['score'] }}
                                                                /{{ $result['totalScore'] }} </h4>
                                                        </div>
                                                        <div class="col md-2">
                                                            <p>{{ __('frontend.Percentage') }}</p>
                                                            <h4 class="f_w_700 "> {{ $result['mark'] }}% </h4>
                                                        </div>
                                                        <div class="col md-2">
                                                            <p>{{ __('frontend.Rating') }}</p>
                                                            <h4 class="f_w_700 theme_text {{ $result['text_color'] }}">

                                                                @if ($result['status'] != 'Failed')
                                                                    {{ __('frontend.Passed') }}
                                                                @else
                                                                    {{ __('frontend.Failed') }}
                                                                @endif
                                                            </h4>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="sumit_skip_btns d-flex align-items-center">
                                    @if (isset($result) && $result['status'] != 'Failed')
                                        <form action="{{ route('lesson.complete') }}" method="post">
                                            @csrf
                                            <input type="hidden" value="{{ $course->id }}" name="course_id">
                                            <input type="hidden" value="{{ $lesson->id }}" name="lesson_id">
                                            <input type="hidden" value="1" name="status">
                                            <button type="submit"
                                                    class="theme_btn    mr_20">{{ __('student.Done') }}</button>
                                        </form>
                                    @endif
                                    @if (count($preResult) != 0)
                                        <button type="button"
                                                class="theme_line_btn  showHistory  mr_20">{{ __('frontend.View History') }}</button>
                                    @endif
                                    <a href="{{ $lesson->lessonQuiz->show_ans_sheet == 1 ? route('quizResultPreview', $_GET['quiz_result_id'] ?? 0) : '#' }}"
                                       data-quiz_test_id="{{ $_GET['quiz_result_id'] ?? 0 }}"
                                       title="{{ $lesson->lessonQuiz->show_ans_sheet != 1 ? __('quiz.Answer Sheet is currently locked by Teacher') : '' }}"
                                       class=" font_1 font_16 f_w_600 theme_text3 ">{{ __('student.See Answer Sheet') }}</a>
                                </div>
                            @else
                                <span>{{ __('quiz.Please wait till completion marking process') }}</span>
                            @endif


                            <div id="historyDiv" class="pt-5 " style="display:none;">
                                @if (count($preResult) != 0)
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>{{ __('common.Date') }}</th>
                                            <th>{{ __('quiz.Marks') }}</th>
                                            <th>{{ __('quiz.Percentage') }}</th>
                                            <th>{{ __('common.Rating') }}</th>
                                            <th>{{ __('common.Details') }}</th>
                                        </tr>
                                        @foreach ($preResult as $pre)
                                            <tr>
                                                <td>{{ $pre['date'] }}</td>
                                                <td>{{ $pre['score'] }}/{{ $pre['totalScore'] }}</td>
                                                <td>{{ $pre['mark'] }}%</td>
                                                <td class="{{ $pre['text_color'] }}">
                                                    @if ($pre['status'] != 'Failed')
                                                        {{ __('frontend.Passed') }}
                                                    @else
                                                        {{ __('frontend.Failed') }}
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ $lesson->lessonQuiz->show_ans_sheet == 1 ? route('quizResultPreview', $pre['quiz_test_id']) : '#' }}"
                                                       data-quiz_test_id="{{ $pre['quiz_test_id'] }}"
                                                       title="{{ $lesson->lessonQuiz->show_ans_sheet != 1 ? __('quiz.Answer Sheet is currently locked by Teacher') : '' }}"
                                                       class=" font_1 font_16 f_w_600 theme_text3    @if ($lesson->lessonQuiz->show_ans_with_explanation == 1)
                                       submit_q_btn
                                       @endif ">{{ __('student.See Answer Sheet') }}</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </table>
                                @endif
                            </div>
                            @if ($lesson->lessonQuiz->show_ans_with_explanation == 1)
                                <div class="mt-3">
                                    <x-quiz-details-question-list :quiz="$lesson->lessonQuiz"/>
                                </div>
                            @endif
                        </div>
                    @else
                        <h3 class="text-center">{{ __('student.Congratulations! You’ve completed') }}
                            {{ $lesson->lessonQuiz->title }}</h3>

                    @endif
                </div>
            @else
                <div class="quiz_questions_wrapper w-100 mt_70 ms-5 me-5">
                    <!-- quiz_test_header  -->

                    @if ($alreadyJoin != 0 && $lesson->lessonQuiz->multiple_attend == 0)
                        <div class="quiz_test_header d-flex justify-content-between align-items-center">
                            <div class="quiz_header_left text-center">
                                <h3>{{ __('frontend.Sorry! You already attempted this quiz') }}</h3>
                            </div>


                        </div>
                    @else
                        <div class="quiz_test_header d-flex justify-content-between align-items-center">
                            <div class="quiz_header_left">
                                <h3>{{ $lesson->lessonQuiz->title }}
                                </h3>
                            </div>

                            <div class="quiz_header_right">

                                <span class="question_time">
                                    @php
                                        $timer = 0;

                                        if (!empty($lesson->lessonQuiz->question_time_type == 1)) {
                                            $timer = $lesson->lessonQuiz->question_time;
                                        } else {
                                            $timer = $lesson->lessonQuiz->question_time * count($questions);
                                        }

                                    @endphp

                                    <span id="timer">{{ $timer }}:00</span> {{ __('quiz.Min') }}</span>
                                <p>{{ __('student.Left of this Section') }}</p>
                            </div>
                        </div>
                        <form action="{{ route('quizSubmit') }}" method="POST" id="quizForm">
                            <input type='hidden' name="from" value="course">
                            <input type="hidden" name="courseId" value="{{ $course->id }}">
                            <input type="hidden" name="quizType" value="1">
                            <input type="hidden" name="quizId" value="{{ $lesson->lessonQuiz->id }}">
                            <input type="hidden" name="question_review" id="question_review"
                                   value="{{ $lesson->lessonQuiz->question_review }}">
                            <input type="hidden" name="start_at" value="">
                            <input type="hidden" name="quiz_test_id" value="">
                            <input type="hidden" name="quiz_start_url" value="{{ route('quizTestStart') }}">
                            <input type="hidden" name="single_quiz_submit_url" value="{{ route('singleQuizSubmit') }}">
                            @csrf

                            <div class="quiz_test_body ">
                                <div class="tabControl">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="tab-content" id="pills-tabContent">
                                                @php
                                                    $count = 1;
                                                @endphp
                                                @if (isset($questions))
                                                    @foreach ($questions as $key => $assign)
                                                        @php
                                                            $options = [];
                                                            if (isset($assign->questionBank->questionMu)) {
                                                                $options = $assign->questionBank->questionMu;
                                                            }
                                                        @endphp
                                                        <div
                                                            class="tab-pane fade  {{ $key == 0 ? 'active show' : '' }} singleQuestion"
                                                            data-qus-id="{{ $assign->id }}"
                                                            data-qus-type="{{ $assign->questionBank->type }}"
                                                            id="pills-{{ $assign->id }}" role="tabpanel"
                                                            aria-labelledby="pills-home-tab{{ $assign->id }}">
                                                            <div class="question_list_header">

                                                            </div>
                                                            <div class="multypol_qustion mb_30">
                                                                <h4 class="font_18 f_w_700 mb-0"> {!! @$assign->questionBank->question !!}
                                                                </h4>
                                                                <small>({{ __('quiz.Choose') }} <span
                                                                        class="questionAnsTotal text-danger fw-bold">
                                                                        {{ count($options->where('status', 1)) }}</span>
                                                                    @if (count($options->where('status', 1)) <= 1)
                                                                        {{ __('quiz.answer') }})
                                                                    @else
                                                                        {{ __('quiz.answers') }})
                                                                    @endif
                                                                </small>
                                                            </div>
                                                            <input type="hidden" class="question_type"
                                                                   name="type[{{ $assign->questionBank->id }}]"
                                                                   value="{{ @$assign->questionBank->type }}">
                                                            <input type="hidden" class="question_id"
                                                                   name="question[{{ $assign->questionBank->id }}]"
                                                                   value="{{ @$assign->questionBank->id }}">

                                                            @if ($assign->questionBank->type == 'M')
                                                                <ul class="quiz_select">
                                                                    @if (isset($assign->questionBank->questionMu))
                                                                        @foreach (@$assign->questionBank->questionMu as $option)
                                                                            <li>
                                                                                <label
                                                                                    class="primary_bulet_checkbox d-flex">
                                                                                    <input class="quizAns"
                                                                                           name="ans[{{ $option->question_bank_id }}][]"
                                                                                           type="checkbox"
                                                                                           value="{{ $option->id }}">

                                                                                    <span
                                                                                        class="checkmark mr_10"></span>
                                                                                    <span
                                                                                        class="label_name">{{ $option->title }}
                                                                                    </span>
                                                                                </label>
                                                                            </li>
                                                                        @endforeach
                                                                    @endif
                                                                </ul>
                                                            @else
                                                                <div style="margin-bottom: 20px;">
                                                                    <textarea class="textArea lms_summernote quizAns"
                                                                              id="editor{{ $assign->id }}" cols="30"
                                                                              rows="10"
                                                                              name="ans[{{ $assign->questionBank->id }}]"></textarea>
                                                                </div>
                                                            @endif
                                                            @if (!empty($assign->questionBank->image))
                                                                <div class="ques_thumb mb_50">
                                                                    <img src="{{ asset($assign->questionBank->image) }}"
                                                                         class="img-fluid" alt="">
                                                                </div>
                                                            @endif
                                                            <div
                                                                class="sumit_skip_btns d-flex align-items-center mb_50">
                                                                @if (count($questions) != $count)
                                                                    <span class="theme_btn small_btn  mr_20 next"
                                                                          data-question_id="{{ $assign->questionBank->id }}"
                                                                          data-assign_id="{{ $assign->id }}"
                                                                          data-question_type="{{ $assign->questionBank->type }}"
                                                                          id="next">{{ __('student.Continue') }}</span>
                                                                    <span
                                                                        class=" font_1 font_16 f_w_600 theme_text3 submit_q_btn skip"
                                                                        id="skip">{{ __('student.Skip') }}
                                                                        {{ __('frontend.Question') }}</span>
                                                                @else
                                                                    <button type="button"
                                                                            data-question_id="{{ $assign->questionBank->id }}"
                                                                            data-assign_id="{{ $assign->id }}"
                                                                            data-question_type="{{ $assign->questionBank->type }}"
                                                                            class="submitBtn theme_btn small_btn  mr_20">
                                                                        {{ __('student.Submit') }}
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        @php
                                                            $count++;
                                                        @endphp
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>

                                        <div class="col-xl-6">

                                            @php
                                                $count2 = 1;
                                            @endphp

                                            <div class="question_list_header">
                                                <div class="question_list_top">
                                                    <p>{{ __('quiz.Question') }} <span
                                                            id="currentNumber">{{ $count2 }}</span>
                                                        {{ __('common.out of') }} {{ count($questions) }}</p>
                                                </div>
                                            </div>
                                            <div class="nav question_number_lists" id="nav-tab" role="tablist">
                                                @if (isset($questions))
                                                    @foreach ($questions as $key2 => $assign)
                                                        <a class="nav-link questionLink link_{{ $assign->id }} {{ $key2 == 0 ? 'skip_qus' : 'pouse_qus' }}"
                                                           data-bs-toggle="tab" href="#pills-{{ $assign->id }}"
                                                           role="tab" aria-controls="nav-home"
                                                           data-qus="{{ $assign->id }}"
                                                           aria-selected="true">{{ $count2 }}</a>
                                                        @php
                                                            $count2++;
                                                        @endphp
                                                    @endforeach
                                                @endif
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    @endif
                </div>



                @include(theme('partials._quiz_submit_confirm_modal'))
                @include(theme('partials._quiz_start_confirm_modal'))
            @endif
        @elseif($lesson->is_assignment == 1)
            @if (isModuleActive('Assignment'))

                @php

                    $assignment_info = $lesson->assignmentInfo;
                    if (Auth::check()) {
                        $submit_info = Modules\Assignment\Entities\InfixSubmitAssignment::assignmentLastSubmitted($assignment_info->id, Auth::user()->id);

                        if (Auth::user()->role_id == 1) {
                            $sty = '-150px';
                        } else {
                            if ($submit_info != null) {
                                $sty = '50px';
                            } else {
                                $sty = '280px';
                            }
                        }
                    } else {
                        $submit_info = null;
                        if ($submit_info != null) {
                            $sty = '50px';
                        } else {
                            $sty = '280px';
                        }
                    }
                @endphp
                <div class="col-lg-12 ps-5">

                    <style>
                        .assignment_info {
                            margin-top: 10px;
                        }
                    </style>
                    <div class="table-responsive-md table-responsive-sm assignment-info-table">
                        <table class="table">
                            <thead>
                            <h3 class="mb-0 ">{{ __('assignment.Assignment') }} {{ __('common.Details') }}</h3>
                            </thead>
                            <tr class="nowrap">
                                <td>
                                    {{ __('common.Title') }}
                                </td>
                                <td>
                                    : {{ @$assignment_info->title }}
                                </td>
                                <td>
                                    {{ __('courses.Course') }}
                                </td>
                                <td>
                                    @if ($assignment_info->course->title)
                                        : {{ @$assignment_info->course->title }}
                                    @else
                                        : {{ __('frontend.Not Assigned') }}
                                    @endif
                                </td>
                            </tr>
                            <tr class="nowrap">
                                <td>
                                    {{ __('assignment.Marks') }}
                                </td>
                                <td>
                                    : {{ @$assignment_info->marks }}
                                </td>
                                <td>
                                    {{ __('assignment.Min Percentage') }}
                                </td>
                                <td>
                                    : {{ @$assignment_info->min_parcentage }}%
                                </td>
                            </tr>
                            @if ($submit_info != null)
                                <tr class="nowrap">
                                    <td>
                                        {{ __('assignment.Obtain Marks') }}
                                    </td>
                                    <td>
                                        : {{ @$submit_info->marks }}
                                    </td>
                                    <td>
                                        {{ __('common.Status') }}
                                    </td>
                                    <td>
                                        :

                                        @if ($submit_info->assigned->pass_status == 1)
                                            {{ __('frontend.Pass') }}
                                        @elseif($submit_info->assigned->pass_status == 2)
                                            {{ __('frontend.Fail') }}
                                        @else
                                            {{ __('frontend.Not Marked') }}
                                        @endif
                                    </td>
                                </tr>
                            @endif

                            <tr>
                                <td>
                                    {{ __('assignment.Submit Date') }}
                                </td>
                                <td>
                                    : {{ showDate(@$assignment_info->last_date_submission) }}
                                </td>
                                <td>
                                    {{ __('assignment.Attachment') }}
                                </td>
                                <td>
                                    @if (file_exists($assignment_info->attachment))
                                        : <a href="{{ asset(@$assignment_info->attachment) }}"
                                             download="{{ @$assignment_info->title }}_attachment">{{ __('common.Download') }}</a>
                                    @endif
                                </td>
                            </tr>

                        </table>
                    </div>


                    <div class="row assignment_info">
                        <div class="col-lg-2">
                            {{ __('assignment.Description') }}
                        </div>
                        <div class="col-lg-12">
                            {!! @$assignment_info->description !!}
                        </div>
                    </div>

                    @php
                        $todate = today()->format('Y-m-d');
                    @endphp
                    @if (empty($submit_info))
                        @if (isset($assignment_info->last_date_submission) && Auth::user()->role_id == 3)
                            @if (
                                $todate <= $assignment_info->last_date_submission ||
                                    (isset($submit_info) && $submit_info->assigned->pass_status == 0))
                                @include(theme('partials._assignment_submit_section'))
                            @endif
                        @else
                            @if (isset($submit_info) && $submit_info->assigned->pass_status == 0 && Auth::user()->role_id == 3)
                                @include(theme('partials._assignment_submit_section'))
                            @endif
                        @endif
                    @endif

                </div>
            @endif
        @else
            <input type="hidden" id="course_id" value="{{ $lesson->course_id }}">
            <script>
                var course_id = document.getElementById('course_id').value;
            </script>
            @if ($lesson->host == 'Youtube')
                @php
                    if (Str::contains($lesson->video_url, '&')) {
                        $video_id = explode('=', $lesson->video_url);
                        $youtube_url = youtubeVideo($video_id[1]);
                    } else {
                        $youtube_url = getVideoId(showPicName(@$lesson->video_url));
                    }
                @endphp
                @if (Settings('youtube_default_player'))
                    <div style="" id="video-placeholder"></div>
                    <input class="d-none" type="text" id="progress-bar">
                    <input type="hidden" name="" id="youtube_video_id" value="{{ $youtube_url }}">

                    @push('js')
                        <script src="https://www.youtube.com/iframe_api" defer></script>
                        <script>
                            var source_video_id = $('#youtube_video_id').val();
                            var player;

                            // val youtube_video_id=$('#youtube_video_id').val();
                            function onYouTubeIframeAPIReady() {
                                player = new YT.Player('video-placeholder', {
                                    videoId: source_video_id,
                                    height: '100%',
                                    width: '100%',
                                    host: '{{ request()->secure() ? 'https' : 'http' }}://www.youtube-nocookie.com',
                                    playerVars: {
                                        color: 'white',
                                        controls: {{ Settings('show_seek_bar') ? 1 : 0 }},
                                        showinfo: 0,
                                        // rel: 0,
                                        modestbranding: 1,
                                        enablejsapi: 1,
                                        iv_load_policy: 3,
                                        html5: 1,
                                        fs: 1,
                                        cc_load_policy: 1,
                                        start: 0,
                                        origin: window.location.host
                                    },
                                    events: {
                                        onReady: initialize

                                    }
                                });

                            }

                            function initialize() {
                                updateTimerDisplay();
                                updateProgressBar();

                                player.playVideo();
                                console.log('play');
                                time_update_interval = setInterval(function () {
                                    updateTimerDisplay();
                                    updateProgressBar();
                                }, 1000)

                            }


                            function updateProgressBar() {
                                $('#progress-bar').val((player.getCurrentTime() / player.getDuration()) * 100);
                            }

                            function updateTimerDisplay() {
                                $('#currentTime').text(formatTime(player.getCurrentTime()));
                                $('#totalTime').text(formatTime(player.getDuration()));
                                //mark as complete before 1 second
                                if (player.getCurrentTime() >= (player.getDuration() - 1)) {
                                    if (!completeRequest) {
                                        lessonAutoComplete(course_id, {{ showPicName(Request::url()) }});
                                        completeRequest = true;
                                    }

                                }
                            }


                            function formatTime(time) {
                                time = Math.round(time);
                                var minutes = Math.floor(time / 60),
                                    seconds = time - minutes * 60;
                                seconds = seconds < 10 ? '0' + seconds : seconds;
                                return minutes + ":" + seconds;
                            }

                            $('#progress-bar').on('mouseup touchend', function (e) {
                                var newTime = player.getDuration() * (e.target.value / 100);
                                player.seekTo(newTime);
                            });

                            function updateProgressBar() {
                                $('#progress-bar').val((player.getCurrentTime() / player.getDuration()) * 100);
                            }

                        </script>
                    @endpush
                @else
                    <link href="{{ asset('public/frontend/nitmtheme/plugins/css/jquery.mb.YTPlayer.min.css') }}"
                          media="all" rel="stylesheet" type="text/css">

                    <script src="{{ asset('public/frontend/nitmtheme/plugins/jquery.mb.YTPlayer.js') }}"></script>

                    <div class="video_iframe d-flex justify-content-center align-items-center" id="video-id"
                         data-property="{videoURL:'http://youtu.be/{{ $youtube_url }}',containment:'self',   mute:false, startAt:0, loop:false, opacity:1,seekbar:{{ Settings('show_seek_bar') ? 'true' : 'false' }}}">
                     Loading...
                    </div>
                    <script>
                        jQuery(function () {
                            $("#video-id").YTPlayer();

                            $("#video-id").on("YTPEnd", function (e) {
                                if (!completeRequest) {
                                    lessonAutoComplete(course_id, {{ showPicName(Request::url()) }});
                                    completeRequest = true;
                                }
                            });
                        });
                    </script>
                @endif
            @endif
            {{-- End Youtube --}}

            @if ($lesson->host == 'Vimeo')
                <div class="video_wrapper">
                    <iframe class="video_iframe" id="video-id"
                            src="https://player.vimeo.com/video/{{ getVideoId(showPicName(@$lesson->video_url)) }}?autoplay=1&"
                            frameborder="0" controls="1" allowfullscreen></iframe>
                </div>
                <script src="https://f.vimeocdn.com/js/froogaloop2.min.js"></script>

                @push('js')
                    <script src='https://player.vimeo.com/api/player.js'></script>
                    <script>
                        $(function () {
                            var iframe = $('#video-id')[0];
                            var player = new Vimeo.Player(iframe);
                            var status = $('.status');


                            player.on('pause', function () {
                                console.log('paused');
                            });

                            player.on('ended', function () {
                                console.log('ended');
                                console.log(completeRequest)
                                if (!completeRequest) {
                                    lessonAutoComplete(course_id, {{ showPicName(Request::url()) }});
                                    completeRequest = true;
                                }
                                status.text('End');


                            });

                            player.on('timeupdate', function (data) {
                                console.log(data.seconds + 's played');
                            });

                        });
                    </script>
                @endpush
            @endif
            @push('js')
                <script>
                    $("#autoNext").change(function () {
                        if ($(this).is(':checked')) {
                            localStorage.setItem('autoNext', 1);
                        } else {
                            localStorage.setItem('autoNext', 0);

                        }

                    });
                    if (localStorage.getItem('autoNext') == 0) {
                        $("#autoNext").prop('checked', false);
                    }
                    $("#autoNext").trigger('change');

                    function lessonAutoComplete(course_id, lesson_id) {
                        let status = $('#single_lesson_' + lesson_id).find('[type=checkbox]');
                        if (status.is(":checked")) {
                            return true;
                        }
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        });


                        $.ajax({
                            type: 'GET',
                            "_token": "{{ csrf_token() }}",
                            url: '{{ route('lesson.complete.ajax') }}',
                            data: {
                                course_id: course_id,
                                lesson_id: lesson_id
                            },
                            success: function (data) {
                                if ($('#autoNext').is(':checked')) {
                                    @if (isModuleActive('Org') && $lesson->host == 'SCORM')
                                    $('#single_lesson_' + lesson_id).find('[type=checkbox]').prop('checked', true);
                                    @else
                                    reaload();
                                    @endif

                                }

                            }
                        });

                        function reaload() {
                            if ($('#next_lesson_btn').length) {
                                jQuery('#next_lesson_btn').click();
                            } else {
                                location.reload();
                            }
                        }

                        if (window.outerWidth < 425) {
                            $('.courseListPlayer').toggleClass("active");
                            $('.course_fullview_wrapper').toggleClass("active");
                        }
                    }
                </script>
            @endpush
            @if ($lesson->host == 'VdoCipher')
                <div id="embedBox" class="video_iframe"></div>

                <script>
                    (function (v, i, d, e, o) {
                        v[o] = v[o] || {
                            add: function V(a) {
                                (v[o].d = v[o].d || []).push(a);
                            }
                        };
                        if (!v[o].l) {
                            v[o].l = 1 * new Date();
                            a = i.createElement(d);
                            m = i.getElementsByTagName(d)[0];
                            a.async = 1;
                            a.src = e;
                            m.parentNode.insertBefore(a, m);
                        }
                    })(
                        window,
                        document,
                        "script",
                        "https://cdn-gce.vdocipher.com/playerAssets/1.6.10/vdo.js",
                        "vdo"
                    );
                    vdo.add({
                        otp: "{{ $lesson->otp }}",
                        playbackInfo: "{{ $lesson->playbackInfo }}",
                        theme: "9ae8bbe8dd964ddc9bdb932cca1cb59a",
                        container: document.querySelector("#embedBox"),
                        autoplay: true
                    });
                </script>

                <script>
                    var isRedirect = false;

                    function onVdoCipherAPIReady() {


                        let video = vdo.getObjects()[0];


                        setInterval(function () {
                            if (video.ended) {
                                if (!isRedirect) {
                                    if (!completeRequest) {
                                        lessonAutoComplete(course_id, {{ showPicName(Request::url()) }});
                                        completeRequest = true;
                                    }
                                    isRedirect = true;
                                }
                            }
                        }, 1000);
                    }
                </script>
            @endif

            @if (isModuleActive('BunnyStorage') && $lesson->host == 'BunnyStorage')
                @php
                    $time = \Illuminate\Support\Carbon::now()
                        ->addDay(1)
                        ->unix();
                    if ($lesson->bunnyLesson && $lesson->bunnyLesson->service_type == 'stream') {
                        $url = 'https://iframe.mediadelivery.net/embed/' . $lesson->bunnyLesson->library_id . '/' . $lesson->bunnyLesson->video_id;
                        $sha256 = hash('sha256', $lesson->bunnyLesson->token_authentication_key . $lesson->bunnyLesson->video_id . $time);
                        $url .= '?token=' . $sha256 . '&expires=' . $time . '&autoplay=true&preload=true';
                        $lesson_src = $url;
                    } elseif ($lesson->bunnyLesson && $lesson->bunnyLesson->service_type == 'storage') {
                        $bunnyStreamController = new \Modules\BunnyStorage\Http\Controllers\BunnyStreamController();
                        $path = 'https://' . $lesson->bunnyLesson->zone_name . '.b-cdn.net/' . $lesson->bunnyLesson->name;
                        $url = $bunnyStreamController->sign_bcdn_url($path, $lesson->bunnyLesson->token_authentication_key, $time);
                        $lesson_src = $url;
                    } else {
                        $lesson_src = $lesson->video_url;
                    }
                @endphp


                <iframe src="{{ $lesson_src }}" loading="lazy" style="border: none; height: 100%; width: 100%;"
                        frameborder="0" controls="1"
                        allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture;"
                        allowfullscreen>
                </iframe>
            @endif

            @if ($lesson->host == 'Self')
                <video class="" id="video-id" controls autoplay
                       onended="lessonAutoComplete(course_id, {{ showPicName(Request::url()) }})">
                    <source src="{{ asset($lesson->video_url) }}" type="video/mp4"/>
                    <source src="{{ asset($lesson->video_url) }}" type="video/ogg">
                </video>
            @endif

      @if ($lesson->host == 'm3u8')
                <video class="" id="video-id" controls autoplay
                       onended="lessonAutoComplete(course_id, {{ showPicName(Request::url()) }})">
                    <source src="{{ $lesson->video_url }}" type='application/x-mpegURL'/>
                </video>
            @endif



            @if ($lesson->host == 'URL')
                <video class="" id="video-id" controls autoplay
                       onended="lessonAutoComplete(course_id, {{ showPicName(Request::url()) }})">
                    <source src="{{ $lesson->video_url }}" type="video/mp4">
                    <source src="{{ $lesson->video_url }}" type="video/ogg">
                    Your browser does not support the video.
                </video>
            @endif
            @if ($lesson->host == 'AmazonS3')
                <video class=" " id="video-id" controls
                       onended="lessonAutoComplete(course_id, {{ showPicName(Request::url()) }})">
                    <source src="{{ $lesson->video_url }}" type="video/mp4"/>

                </video>
            @endif
            @if ($lesson->host == 'H5P' && isModuleActive('H5P'))
                @include('h5p::player', ['course' => $course, 'lesson' => $lesson])
            @endif
            @if ($lesson->host == 'XAPI' || $lesson->host == 'XAPI-AwsS3')
                <iframe id="video-id" class="video_iframe"
                        src="{{ asset($lesson->video_url) }}?actor=%7B%22mbox%22%3A%22mailto%3A{{ Settings('email') }}%22%2C%22name%22%3A%22{{ Settings('site_title') }}%22%2C%22objectType%22%3A%22Agent%22%7D&amp;endpoint={{ url('xapi') }}&amp;course_id={{ $course->id }}&amp;lesson_id={{ $lesson->id }}&amp;next_lesson={{ $lesson_ids[$current_index + 1] ?? '' }}"></iframe>
            @endif
            @if ($lesson->host == 'SCORM' || $lesson->host == 'SCORM-AwsS3')
                @if (!empty($lesson->video_url))
                    <iframe class=" video_iframe" id="video-id" src=""
                            @if ($lesson->scorm_version == 'scorm_12') onbeforeunload="API.LMSFinish('');" width="100%"
                            height="100%" onunload="API.LMSFinish('');" @endif></iframe>
                @endif
            @endif

            @if ($lesson->host == 'Iframe')
                @if (!empty($lesson->video_url))
                    <iframe class="video_iframe" id="video-id" src="{{ asset($lesson->video_url) }}"></iframe>
                @endif
            @endif


            @if ($lesson->host == 'Image')
                <img src="{{ asset($lesson->video_url) }}" alt="" class="w-100  h-100">
            @endif

            @if ($lesson->host == 'PDF')
                <script src="{{ asset('public/frontend/nitmtheme/js/pdf.min.js') }}"></script>
                <script src="{{ asset('public/frontend/nitmtheme/js/pdfjs-viewer.js') }}"></script>
                <script src="{{ asset('public/frontend/nitmtheme/js/zoom.js') }}"></script>
                <link rel="stylesheet" href="{{ asset('public/frontend/nitmtheme/css/pdfjs-viewer.css') }}"/>
                <style>
                    .pdfjs-viewer.h-100 {
                        max-height: calc(100vh - 50px);
                        overflow: auto;
                    }

                    .small_btn_icon {
                        padding: 10px;
                    }
                </style>

                <script>
                    var pdfjsLib = window['pdfjs-dist/build/pdf'];
                    pdfjsLib.GlobalWorkerOptions.workerSrc = '{{ asset('public/frontend/nitmtheme/js/pdf.worker.min.js') }}';
                </script>
                <div style="border: none;min-height: 400px" class="pdfviewer w-100  h-100">
                    <div class="pdftoolbar text-center row m-0 p-0">
                        <div class="col-12 col-lg-12 my-1">
                            <button class="theme_btn small_btn_icon btn-first" onclick="pdfViewer.first()"><i
                                    class="fa fa-step-backward"></i></button>
                            <button class="theme_btn small_btn_icon btn-prev" onclick="pdfViewer.prev(); return false;">
                                <i class="fa fa-angle-left"></i></button>
                            <span class="pageno"></span>
                            <button class="theme_btn small_btn_icon btn-next" onclick="pdfViewer.next(); return false;">
                                <i class="fa fa-angle-right"></i></button>
                            <button class="theme_btn small_btn_icon btn-last" onclick="pdfViewer.last()"><i
                                    class="fa fa-step-forward"></i></button>
                            <button class="theme_btn small_btn_icon" onclick="pdfViewer.setZoom('out')"><i
                                    class="fa fa-search-minus"></i></button>
                            <span class="zoomval">100%</span>
                            <button class="theme_btn small_btn_icon" onclick="pdfViewer.setZoom('in')"><i
                                    class="fa fa-search-plus"></i></button>
                            <button class="theme_btn small_btn_icon ms-3" onclick="pdfViewer.setZoom('width')"><i
                                    class="fa fa-arrows-alt-h"></i></button>
                            <button class="theme_btn small_btn_icon" onclick="pdfViewer.setZoom('height')"><i
                                    class="fa fa-arrows-alt-v"></i></button>
                            <button class="theme_btn small_btn_icon" onclick="pdfViewer.setZoom('fit')"><i
                                    class="fa fa-expand"></i></button>
                        </div>
                    </div>
                    <div class="pdfjs-viewer h-100">
                    </div>
                </div>

                <script>
                    let pdfViewer = new PDFjsViewer($('.pdfjs-viewer'), {
                        setZoom: -1,
                        maxImageSize: -1,
                        onZoomChange: function (zoom) {
                            zoom = parseInt(zoom * 10000) / 100;
                            $(".zoomval").text(zoom + "%");
                        },
                        onActivePageChanged: function (page, pageno) {
                            $(".pageno").text(pageno + "/" + this.getPageCount());
                        }

                    });
                    pdfViewer.loadDocument("{{ asset($lesson->video_url) }}").then(function () {
                        // pdfViewer.setZoom('width');
                    });
                    enablePinchZoom(pdfViewer)
                </script>
            @endif
            @if ($lesson->host == 'Word')
                <iframe class="w-100  h-100 mobile-min-height"
                        src="https://docs.google.com/gview?url={{ asset($lesson->video_url) }}&embedded=true"></iframe>
            @endif
            @if ($lesson->host == 'Excel' || $lesson->host == 'PowerPoint')
                <iframe class="w-100  h-100 mobile-min-height"
                        src="https://view.officeapps.live.com/op/view.aspx?src={{ asset($lesson->video_url) }}"></iframe>
            @endif

            @if ($lesson->host == 'GoogleDrive')
                {{--                <iframe class="w-100  h-100" controlsList="nodownload"--}}
                {{--                        src="https://drive.google.com/uc?id={{ $lesson->video_url }}&export=view"></iframe>--}}
                <iframe class="w-100  h-100" controlsList="nodownload"
                        src="https://drive.google.com/file/d/{{$lesson->video_url}}/preview"></iframe>
            @endif

            @if ($lesson->host == 'Text')
                <div class="w-100  h-100 textViewer">

                </div>
                <script>
                    $(".textViewer").load("{{ asset($lesson->video_url) }}");
                </script>
            @endif


            {{-- Iframe video --}}
            @push('js')
                @if ($lesson->host == 'Iframe')
                    <script>
                        $(document).ready(function (e) {
                            if ($('#video-id').length) {
                                var iframe = document.getElementById("video-id");
                                // console.log(iframe);
                                var video = iframe.contentDocument.body.getElementsByTagName("video")[0];
                                var supposedCurrentTime = 0;
                                video.addEventListener('timeupdate', function () {
                                    if (!video.seeking) {
                                        supposedCurrentTime = video.currentTime;
                                    }
                                });
                                // prevent user from seeking
                                video.addEventListener('seeking', function () {
                                    // guard agains infinite recursion:
                                    // user seeks, seeking is fired, currentTime is modified, seeking is fired, current time is modified, ....
                                    var delta = video.currentTime - supposedCurrentTime;
                                    if (Math.abs(delta) > 0.01) {
                                        console.log("Seeking is disabled");
                                        video.currentTime = supposedCurrentTime;
                                    }
                                });
                                // delete the following event handler if rewind is not required
                                video.addEventListener('ended', function () {
                                    if (!completeRequest) {
                                        lessonAutoComplete(course_id, {{ showPicName(Request::url()) }});
                                        completeRequest = true;
                                    }

                                    // reset state in order to allow for rewind
                                    console.log('video end');
                                    supposedCurrentTime = 0;
                                });
                            }
                        });
                    </script>
                @endif
            @endpush
            @if ($lesson->host == 'Zip')
                <style>
                    .parent {
                        position: fixed;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }

                    .child {
                        position: relative;
                        font-size: 10vw;
                    }
                </style>
                <div class="w-100 parent  h-100 ">
                    <div class="">
                        <div class="row">
                            <div class="col  text-center">
                                <div class="child">
                                    <a class="theme_btn " href="{{ asset($lesson->video_url) }}"
                                       download="">{{ __('frontend.Download File') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            @endif

        @endif
        {{-- </div> --}}


        <input type="hidden" id="url" value="{{ url('/') }}">
        <div class="floating-title position-fixed">
            <p class="font_16 d-flex align-items-center">
                <span class="header__common_btn me-2 play_toggle_btn"><i
                        class="ti-menu-alt"></i></span> {{ @$total }} {{ __('common.Lessons') }}
            </p>
        </div>
        <div class="course__play_warp courseListPlayer ">
            <div class="play_warp_header d-flex justify-content-between">
                <h3 class="font_16 mb-0 lesson_count default-font d-flex align-items-center">
                    <span class="play_toggle_btn header__common_btn me-2 d-none d-lg-flex">
                        <i class="fas fa-expand"></i>
                    </span>
                    {{-- <a href="{{ courseDetailsUrl(@$course->id, @$course->type, @$course->slug) }}"
                        class="theme_btn_mini">
                        <i class="fas fa-arrow-left"></i>
                    </a> --}}
                    <span>
                        <strong class="d-block d-lg-none">{{ $course->title }}</strong>
                        <span class="d-block">
                            {{ @$total }} {{ __('common.Lessons') }}
                        </span>
                    </span>
                </h3>
                <button class="theme-btn p-2" type="button" data-bs-toggle="modal"
                        data-bs-target="#qnamodal">{{__('common.Q&A')}}</button>

            </div>
            <div class="course__play_list">
                @php
                    $i = 1;
                @endphp
                <div class="theme_according mb_30 accordion" id="accordion1">
                    @foreach ($chapters as $k => $chapter)
                        <div class="accordion-item">
                            <div class="accordion-header" id="heading{{ $chapter->id }}">
                                <h5 class="mb-0">
                                    <button class="accordion-button collapsed" data-bs-toggle="collapse"
                                            data-bs-target="#collapse{{ $chapter->id }}" aria-expanded="false"
                                            aria-controls="collapse{{ $chapter->id }}">
                                        {{ $chapter->name }} <br>
                                        <span class="course_length nowrap">
                                            @if (!isModuleActive('Assignment'))
                                                {{ count($chapter->lessons->where('is_assignment', 0)) }}
                                            @else
                                                {{ count($chapter->lessons) }}
                                            @endif

                                            {{ __('frontend.Lectures') }}
                                        </span>
                                    </button>
                                </h5>
                            </div>
                            <div class="collapse" id="collapse{{ $chapter->id }}"
                                 aria-labelledby="heading{{ $chapter->id }}" data-bs-parent="#accordion1">
                                <div class="accordion-body">
                                    <div class="curriculam_list">
                                        @if (isset($lessons))
                                            @php
                                                // $video_lesson_hosts=['Youtube','Vimeo','Self','URL'];
                                            @endphp
                                            @foreach ($lessons as $key => $singleLesson)
                                                @if ($singleLesson->chapter_id == $chapter->id)
                                                    @php
                                                        if ($singleLesson->is_quiz == 1 && $singleLesson->quiz->count() == 0) {
                                                            continue;
                                                        }
                                                        if ($singleLesson->is_assignment == 1 && !isModuleActive('Assignment')) {
                                                            continue;
                                                        }
                                                    @endphp
                                                    <div class="single_play_list"
                                                         id="single_lesson_{{ $singleLesson->id }}">
                                                        <a class="@if (showPicName(Request::url()) == $singleLesson->id) active @endif"
                                                           href="#">

                                                            @if ($singleLesson->is_quiz == 1)
                                                                <div class="course_play_name">

                                                                    <label class="primary_checkbox d-flex mb-0">
                                                                        <input type="checkbox"
                                                                               {{ $singleLesson->completed && $singleLesson->completed->status == 1 ? 'checked' : '' }}
                                                                               disabled>
                                                                        <span class="checkmark mr_15"
                                                                              style="cursor: not-allowed"></span>

                                                                        <i class="ti-check-box"></i>
                                                                    </label>
                                                                    @foreach ($singleLesson->quiz as $quiz)
                                                                        <span class="quizLink"
                                                                              onclick="goFullScreen({{ $course->id }},{{ $singleLesson->id }})">
                                                                            <span class="quiz_name">{{ $i }}.
                                                                                {{ @$quiz->title }}</span>
                                                                        </span>
                                                                </div>
                                                                @endforeach
                                                            @else
                                                                <div class="course_play_name">
                                                                    @if (request()->route('lesson_id') == $singleLesson->id)
                                                                        <div
                                                                            class="remember_forgot_pass d-flex justify-content-between">
                                                                            <label class="primary_checkbox d-flex mb-0">
                                                                                @if ($isEnrolled)
                                                                                    <input type="checkbox"
                                                                                           {{ $singleLesson->completed && $singleLesson->completed->status == 1 ? 'checked' : '' }}
                                                                                           disabled>
                                                                                    <span style="cursor: not-allowed"
                                                                                          class="checkmark mr_15"></span>
                                                                                    <i class="ti-control-play"></i>
                                                                                @else
                                                                                    <i class="ti-control-play"></i>
                                                                                @endif
                                                                            </label>
                                                                        </div>
                                                                    @else
                                                                        <label class="primary_checkbox d-flex mb-0">
                                                                            <input type="checkbox"
                                                                                {{ $singleLesson->completed && $singleLesson->completed->status == 1 ? 'checked' : '' }}>
                                                                            <span style="cursor: not-allowed"
                                                                                  class="checkmark mr_15"></span>

                                                                            <i class="ti-control-play"></i>
                                                                        </label>
                                                                    @endif

                                                                    <span
                                                                        onclick="goFullScreen({{ $course->id }},{{ $singleLesson->id }})">{{ $i }}.
                                                                    {{ $singleLesson->name }} </span>
                                                                </div>
                                                                <span
                                                                    class="course_play_duration nowrap">{{ MinuteFormat($singleLesson->duration) }}</span>
                                                            @endif
                                                        </a>
                                                    </div>
                                                    @php
                                                        $i++;
                                                    @endphp
                                                @endif
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="row justify-content-center text-center">
                    @if ($certificate && !Settings('manually_assign_certificate'))
                        @if ($quizPass)
                            @auth()
                                @if ($percentage >= 100)
                                    @if (isModuleActive('Survey') && $course->survey)
                                        @if (Settings('must_survey_before_certificate'))
                                            @if (auth()->user()->attendSurvey($course->survey))
                                                <a href="{{ route('getCertificate', [$course->id, $course->title]) }}"
                                                   class="theme_btn certificate_btn mt-5 mb-5">
                                                    {{ __('frontend.Get Certificate') }}
                                                </a>
                                                @if (isModuleActive('MyClass'))
                                                    <a href="{{ route('get-transcript', [$course->id, auth()->user()->id]) }}"
                                                       class="theme_btn certificate_btn mt-5 mb-5 ms-2"
                                                       target="__blank">{{ __('class.Get Transcript') }}</a>
                                                @endif
                                            @else
                                                <button type="button" data-bs-toggle="modal"
                                                        data-bs-target="#assignSubmit"
                                                        class="theme_btn certificate_btn mt-5 mb-5">
                                                    {{ __('frontend.Survey') }}
                                                </button>
                                                <small>
                                                    {{ __('frontend.You must attend survey before getting certificate') }}
                                                </small>
                                            @endif
                                        @else
                                            @if (!auth()->user()->attendSurvey($course->survey))
                                                <button type="button" data-bs-toggle="modal"
                                                        data-bs-target="#assignSubmit"
                                                        class="theme_btn certificate_btn mt-5 mb-5 me-1">
                                                    {{ __('frontend.Survey') }}
                                                </button>
                                            @endif
                                            <a href="{{ route('getCertificate', [$course->id, $course->title]) }}"
                                               class="theme_btn certificate_btn mt-5 mb-5 ms-1">
                                                {{ __('frontend.Get Certificate') }}
                                            </a>
                                            @if (isModuleActive('MyClass'))
                                                <a href="{{ route('get-transcript', [$course->id, auth()->user()->id]) }}"
                                                   class="theme_btn certificate_btn mt-5 mb-5 ms-2"
                                                   target="__blank">{{ __('class.Get Transcript') }}</a>
                                            @endif
                                        @endif
                                    @else
                                        <a href="{{ route('getCertificate', [$course->id, $course->title]) }}"
                                           class="theme_btn certificate_btn mt-5 mb-5">
                                            {{ __('frontend.Get Certificate') }}
                                        </a>
                                        @if (isModuleActive('MyClass'))
                                            <a href="{{ route('get-transcript', [$course->id, auth()->user()->id]) }}"
                                               class="theme_btn certificate_btn mt-5 mb-5 ms-2"
                                               target="__blank">{{ __('class.Get Transcript') }}</a>
                                        @endif
                                    @endif
                                @endif
                            @endauth
                        @endif
                    @endif

                </div>
                <div class="pb-5 mb-5 d-none">
                    <div>{{ __('frontend.Current Time') }}: <span id="currentTime">0</span></div>
                    <div>{{ __('frontend.Total Time') }} : <span id="totalTime">0</span></div>
                    <div>{{ __('frontend.Status') }} : <span class="status"></span></div>
                </div>
            </div>
        </div>

    </div>


    <div class="modal fade " id="ShareLink" tabindex="-1" role="dialog" aria-labelledby=" " aria-hidden="true">
        <div class="modal-dialog modal-lg " role="document">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ __('frontend.Share this course') }}

                    </h5>
                </div>

                <div class="modal-body">


                    <div class="row mb-20">
                        <div class="col-md-12">
                            <input type="text" required class="primary_input mb_20" name=""
                                   value="{{ URL::current() }}">
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <div class="social_btns ">
                                <a target="_blank"
                                   href="https://www.facebook.com/sharer/sharer.php?u={{ URL::current() }}"
                                   class="social_btn fb_bg"> <i class="fab fa-facebook-f"></i>
                                </a>
                                <a target="_blank"
                                   href="https://twitter.com/intent/tweet?text={{ $course->title }}&amp;url={{ URL::current() }}"
                                   class="social_btn Twitter_bg"> <i class="fab fa-twitter"></i> </a>
                                <a target="_blank"
                                   href="https://pinterest.com/pin/create/link/?url={{ URL::current() }}&amp;description={{ $course->title }}"
                                   class="social_btn Pinterest_bg"> <i class="fab fa-pinterest-p"></i> </a>
                                <a target="_blank"
                                   href="https://www.linkedin.com/shareArticle?mini=true&amp;url={{ URL::current() }}&amp;title={{ $course->title }}&amp;summary={{ $course->title }}"
                                   class="social_btn Linkedin_bg"> <i class="fab fa-linkedin-in"></i> </a>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>


    <div class="modal fade " id="courseRating" tabindex="-1" role="dialog" aria-labelledby=" " aria-hidden="true">
        <div class="modal-dialog modal-lg " role="document">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ __('frontend.Rate this course') }}

                    </h5>
                </div>
                <div class="modal-body">


                    <div class="row mb-20">
                        <div class="col-md-12">
                            <div class="rating_star text-end">

                                @php
                                    $PickId = $course->id;
                                @endphp
                                @if (Auth::check())
                                    @if (Auth::user()->role_id == 3)
                                        @if (!in_array(Auth::user()->id, $reviewer_user_ids))
                                            <div class="star_icon d-flex align-items-center justify-content-between">
                                                <a class="rating">
                                                    <input type="radio" id="star5" name="rating" value="5"
                                                           class="rating"/><label class="full" for="star5"
                                                                                  id="star5" title="Awesome - 5 stars"
                                                                                  onclick="Rates(5, {{ @$PickId }})"></label>

                                                    <input type="radio" id="star4" name="rating" value="4"
                                                           class="rating"/><label class="full" for="star4"
                                                                                  title="Pretty good - 4 stars"
                                                                                  onclick="Rates(4, {{ @$PickId }})"></label>

                                                    <input type="radio" id="star3" name="rating" value="3"
                                                           class="rating"/><label class="full" for="star3"
                                                                                  title="Meh - 3 stars"
                                                                                  onclick="Rates(3, {{ @$PickId }})"></label>
                                                    <input type="radio" id="star2" name="rating" value="2"
                                                           class="rating"/><label class="full" for="star2"
                                                                                  title="Kinda bad - 2 stars"
                                                                                  onclick="Rates(2, {{ @$PickId }})"></label>

                                                    <input type="radio" id="star1" name="rating" value="1"
                                                           class="rating"/><label class="full" for="star1"
                                                                                  title="Bad  - 1 star"
                                                                                  onclick="Rates(1,{{ @$PickId }})"></label>

                                                </a>
                                            </div>
                                        @endif
                                    @endif
                                @else
                                    <p class="font_14 f_w_400 mt-0"><a href="{{ url('login') }}"
                                                                       class="theme_color2">{{ __('frontend.Sign In') }}</a>
                                        {{ __('frontend.or') }} <a class="theme_color2"
                                                                   href="{{ url('register') }}">{{ __('frontend.Sign Up') }}</a>
                                        {{ __('frontend.as student to post a review') }}</p>
                                @endif

                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="modal cs_modal fade admin-query" id="myModal" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('frontend.Review') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"><i class="ti-close "></i></button>
                </div>

                <form action="{{ route('submitReview') }}" method="Post">
                    <div class="modal-body">
                        @csrf
                        <input type="hidden" name="course_id" id="rating_course_id" value="">
                        <input type="hidden" name="rating" id="rating_value" value="">

                        <div class="text-center">
                            <textarea class="form-control" name="review" name="" id=""
                                      placeholder="{{ __('frontend.Write your review') }}" cols="30"
                                      rows="10">{{ old('review') }}</textarea>
                            <span class="text-danger" role="alert">{{ $errors->first('review') }}</span>
                        </div>


                    </div>
                    <div class="modal-footer justify-content-center">
                        <div class="mt-40">
                            <button type="button" class="theme_line_btn me-2"
                                    data-bs-dismiss="modal">{{ __('common.Cancel') }}
                            </button>
                            <button class="theme_btn " type="submit">{{ __('common.Submit') }}</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>

    @include(theme('partials._qna_modal'))
    <div id="logDisplay">
    </div>
    @if (isModuleActive('Survey') && $course->survey)
        @include(theme('partials._survey_model'))
    @endif

@endsection
@push('js')

    <script>
        $(document).ready(function () {
            if ($('.active').length) {
                let active = $('.active');
                let parent = active.parents('.collapse').first();
                parent.addClass('show');
            }
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $(document).ready(function () {
            let course = '{{ $course->id }}';
            let lesson = '{{ $lesson->id }}';

            /*       $("iframe").each(function () {
                       //Using closures to capture each one
                       var iframe = $(this);
                       iframe.on("load", function () { //Make sure it is fully loaded
                           iframe.contents().click(function (event) {
                               iframe.trigger("click");
                           });

                       });

                       iframe.click(function () {
                           $.ajax({
                               type: 'POST',
                               "_token": "{{ csrf_token() }}",
                            url: '{{ route('lesson.complete.ajax') }}',
                            data: {course_id: course, lesson_id: lesson},
                            success: function (data) {

                            }
                        });
                    });
                });*/

            if (window.outerWidth < 425) {
                $('.courseListPlayer').toggleClass("active");
                $('.course_fullview_wrapper').toggleClass("active");
            }


            $(".completeAndPlayNext").click(function () {
                $.ajax({
                    type: 'POST',
                    "_token": "{{ csrf_token() }}",
                    url: '{{ route('lesson.complete.ajax') }}',
                    data: {
                        course_id: course,
                        lesson_id: lesson
                    },
                    success: function (data) {
                        if ($('#next_lesson_btn').length) {
                            $('#next_lesson_btn').trigger('click');
                        } else {
                            location.reload();
                        }
                    }
                });
            });
        });
    </script>

    @if ($lesson->host == 'Self' || $lesson->host == 'm3u8' || $lesson->host == 'AmazonS3' || $lesson->host == 'URL')
        <script>
            let myFP = fluidPlayer(
                'video-id', {
                    "layoutControls": {
                        "controlBar": {
                            "autoHideTimeout": 3,
                            "animated": true,
                            "autoHide": true
                        },
                        "htmlOnPauseBlock": {
                            "html": null,
                            "height": null,
                            "width": null
                        },
                        "autoPlay": true,
                        "mute": false,
                        "hideWithControls": false,
                        "allowTheatre": false,
                        "playPauseAnimation": true,
                        "playbackRateEnabled": false,
                        "allowDownload": false,
                        "playButtonShowing": true,
                        "fillToContainer": true,
                        "posterImage": ""
                    },
                    "vastOptions": {
                        "adList": [],
                        "adCTAText": false,
                        "adCTATextPosition": ""
                    }
                });
        </script>

        @if (!Settings('show_seek_bar'))
            <style>
                div#video-id_fluid_controls_progress_container {
                    display: none;
                }
            </style>
            <script>
                if ($('#video-id').length) {
                    var video = document.getElementById('video-id');
                    var supposedCurrentTime = 0;
                    video.addEventListener('timeupdate', function () {
                        if (!video.seeking) {
                            supposedCurrentTime = video.currentTime;
                        }
                    });
                    // prevent user from seeking
                    video.addEventListener('seeking', function () {
                        // guard agains infinite recursion:
                        // user seeks, seeking is fired, currentTime is modified, seeking is fired, current time is modified, ....
                        var delta = video.currentTime - supposedCurrentTime;
                        if (Math.abs(delta) > 0.01) {
                            console.log("Seeking is disabled");
                            video.currentTime = supposedCurrentTime;
                        }
                    });
                    // delete the following event handler if rewind is not required
                    video.addEventListener('ended', function () {
                        // reset state in order to allow for rewind
                        console.log('video end');
                        if (!completeRequest) {
                            lessonAutoComplete(course_id, {{ showPicName(Request::url()) }});
                            completeRequest = true;
                        }

                        supposedCurrentTime = 0;
                    });
                }
            </script>
        @endif
    @endif

    <script src="{{ asset('public/frontend/nitmtheme/js/class_details.js') }}"></script>
    <script src="{{ asset('public/frontend/nitmtheme/js/full_screen_video.js') }}"></script>
    @if ($lesson->is_quiz == 1)
        @if (!$result)
            <script src="{{ asset('public/frontend/nitmtheme/js/quiz_start.js') }}"></script>
        @endif
        @include(theme('partials._quiz_exp_script'))
    @endif
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"
            integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl"
            crossorigin="anonymous"></script>

    <script src="{{ asset('public/backend/js/summernote-bs4.min.js') }}"></script>


    <script>
        function sendFile(files, editor = '#summernote', name) {
            let url = '{{url('/')}}';
            let formData = new FormData();
            $.each(files, function (i, v) {
                formData.append("files[" + i + "]", v);
            })

            $.ajax({
                url: url + '/summer-note-file-upload',
                type: 'post',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'JSON',
                success: function (response) {
                    let $summernote;
                    if (name) {
                        $summernote = $(editor + "[name='" + name + "']");
                    } else {
                        $summernote = $(editor);
                    }
                    $.each(response, function (i, v) {
                        $summernote.summernote('insertImage', v);
                    })
                },
                error: function (data) {
                    if (data.status === 404) {
                        toastr.error("What you are looking is not found", 'Opps!');
                        return;
                    } else if (data.status === 500) {
                        toastr.error('Something went wrong. If you are seeing this message multiple times, please contact administrator.', 'Opps');
                        return;
                    } else if (data.status === 200) {
                        toastr.error('Something is not right', 'Error');
                        return;
                    }
                    let jsonValue = $.parseJSON(data.responseText);
                    let errors = jsonValue.errors;
                    if (errors) {
                        let i = 0;
                        $.each(errors, function (key, value) {
                            let first_item = Object.keys(errors)[i];
                            let error_el_id = $('#' + first_item);
                            if (error_el_id.length > 0) {
                                error_el_id.parsley().addError('ajax', {
                                    message: value, updateClass: true
                                });
                            }
                            toastr.error(value, 'Validation Error');
                            i++;
                        });
                    } else {
                        toastr.error(jsonValue.message, 'Opps!');
                    }

                }
            });
        }

        if ($('.lms_summernote').length) {
            $('.lms_summernote').summernote({
                codeviewFilter: true,
                codeviewIframeFilter: true,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen']],
                ],
                placeholder: '',
                tabsize: 2,
                height: 188,
                callbacks: {
                    onImageUpload: function (files) {
                        sendFile(files, '.lms_summernote', $(this).attr('name'))
                    }
                },
                tooltip: false
            });
            $(document).ready(function () {
                $('.note-toolbar').find('[data-toggle]').each(function () {
                    $(this).attr('data-bs-toggle', $(this).attr('data-toggle')).removeAttr('data-toggle');
                });
            });
            $(document).ready(function () {
                $('.note-modal').find('[data-dismiss]').each(function () {
                    $(this).attr('data-bs-dismiss', $(this).attr('data-dismiss')).removeAttr('data-dismiss');
                });
            });
        }
        var app_debug = $('.app_debug').val();
        if (!app_debug) {
            $(document).bind("contextmenu", function (e) {
                e.preventDefault();
            });

            $(document).keydown(function (e) {
                if (e.which === 123) {
                    return false;
                }
            });


            document.onkeydown = function (e) {
                if (event.keyCode == 123) {
                    return false;
                }

                if (e.ctrlKey && e.shiftKey && e.keyCode == 'I'.charCodeAt(0)) {
                    return false;
                }


                if (e.ctrlKey && e.keyCode == 'E'.charCodeAt(0)) {
                    return false;
                }

                if (e.ctrlKey && e.shiftKey && e.keyCode == 'J'.charCodeAt(0)) {
                    return false;
                }
                if (e.ctrlKey && e.keyCode == 'U'.charCodeAt(0)) {
                    return false;
                }
                if (e.ctrlKey && e.keyCode == 'S'.charCodeAt(0)) {
                    return false;
                }
                if (e.ctrlKey && e.keyCode == 'H'.charCodeAt(0)) {
                    return false;
                }

                if (e.ctrlKey && e.keyCode == 'F'.charCodeAt(0)) {
                    return false;
                }
                if (e.ctrlKey && e.keyCode == 'E'.charCodeAt(0)) {
                    return false;
                }
            }
        }
    </script>
    @if ($lesson->host == 'XAPI' || $lesson->host == 'XAPI-AwsS3')
        <script>
            @if (!isset($lesson->completed->status))

            function checkCompleteStatus() {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                var course_id = "{{ $course->id }}";
                var lesson_id = "{{ $lesson->id }}";
                $.ajax({
                    type: 'POST',
                    url: '{{ route('xapi.checkLessonStatus') }}',
                    data: {
                        course_id: course_id,
                        lesson_id: lesson_id
                    },
                    success: function (data) {
                        if (data == 1) {
                            if ($('#autoNext').is(':checked')) {
                                if ($('#next_lesson_btn').length) {
                                    jQuery('#next_lesson_btn').click();
                                } else {
                                    location.reload();
                                }
                            }
                        }
                    }
                });
            }

            setInterval(checkCompleteStatus, 2000)
            @endif
        </script>
    @endif

    @if (
        $lesson->host == 'SCORM' ||
            $lesson->host == 'SCORM-AwsS3' ||
            $lesson->host == 'XAPI' ||
            $lesson->host == 'XAPI-AwsS3')
        <script>
            let video_element = $('#video-id');
            let url = "{{ asset($lesson->video_url) }}";
            @auth
            let full_name = "{{ auth()->user()->name }}";
            @if (isModuleActive('Org'))
            let org_chart_name = "{{ auth()->user()->branch->group }}";
            @endif
            @endauth
            @guest()
            let full_name = "Guest";
            let org_chart_name = "";
            @endguest
            let course_name = "{{ $course->title }}";




            @if ($lesson->scorm_version == 'scorm_12')

            var API = {};

            (function ($) {
                $(document).ready(function () {
                    setupScormApi()
                    video_element.attr('src', url)
                });

                function setupScormApi() {
                    API.LMSInitialize = LMSInitialize;
                    API.LMSGetValue = LMSGetValue;
                    API.LMSSetValue = LMSSetValue;
                    API.LMSCommit = LMSCommit;
                    API.LMSFinish = LMSFinish;
                    API.LMSGetLastError = LMSGetLastError;
                    API.LMSGetDiagnostic = LMSGetDiagnostic;
                    API.LMSGetErrorString = LMSGetErrorString;
                }

                function LMSInitialize(initializeInput) {
                    displayLog("LMSInitialize: " + initializeInput);
                    return true;
                }

                function LMSGetValue(varname) {


                    displayLog("LMSGetValue: " + varname);
                    return varname;
                }

                function LMSSetValue(varname, varvalue) {
                    updateScormReport(varname, varvalue);
                    if (varvalue == 'completed' || varvalue == 'passed') {
                        lessonAutoComplete(course_id, {{ showPicName(Request::url()) }});
                    }
                    // displayLog("LMSSetValue: " + varname + "=" + varvalue);
                    return "";
                }

                function LMSCommit(commitInput) {
                    displayLog("LMSCommit: " + commitInput);
                    return true;
                }

                function LMSFinish(finishInput) {
                    lessonAutoComplete(course_id, {{ showPicName(Request::url()) }});
                    displayLog("LMSFinish: " + finishInput);
                    return true;
                }

                function LMSGetLastError() {
                    displayLog("LMSGetLastError: ");
                    return 0;
                }

                function LMSGetDiagnostic(errorCode) {
                    displayLog("LMSGetDiagnostic: " + errorCode);
                    return "";
                }

                function LMSGetErrorString(errorCode) {
                    displayLog("LMSGetErrorString: " + errorCode);
                    return "";
                }

            })(jQuery);
            @elseif ($lesson->scorm_version == 'scorm_2004')

            var API_1484_11 = {};

            (function ($) {
                $(document).ready(function () {
                    setupScormApi();
                    video_element.attr('src', url)
                });

                function setupScormApi() {
                    API_1484_11.Initialize = Initialize;
                    API_1484_11.Commit = Commit;
                    API_1484_11.Terminate = Terminate;
                    API_1484_11.GetValue = GetValue;
                    API_1484_11.SetValue = SetValue;
                    API_1484_11.GetErrorString = GetErrorString;
                    API_1484_11.GetDiagnostic = GetDiagnostic;
                    API_1484_11.GetLastError = GetLastError;
                }

                function Initialize(parameter) {
                    displayLog('Initialize ' + parameter)
                    return true
                }

                function Commit(parameter) {
                    displayLog('Commit ' + parameter)
                    return true
                }

                function Terminate(parameter) {
                    {{-- lessonAutoComplete(course_id, {{showPicName(Request::url())}}); --}}
                    displayLog('Terminate ' + parameter)
                    return true
                }

                function GetValue(name) {
                    displayLog('GetValue ' + name)
                    return "";
                }

                function SetValue(name, value) {
                    updateScormReport(name, value);
                    if (value == 'completed' || value == 'passed') {
                        lessonAutoComplete(course_id, {{ showPicName(Request::url()) }});
                    }
                    displayLog('SetValue ' + name + ' = ' + value)
                    return true
                }

                function GetErrorString() {
                    displayLog('GetErrorString')
                    return ''
                }

                function GetDiagnostic() {
                    displayLog('GetDiagnostic')
                    return ''
                }

                function GetLastError() {
                    displayLog('GetLastError')
                    return 0
                }


            })(jQuery);
            @endif


            function displayLog(textToDisplay) {
                console.log(textToDisplay);
            }

            @if (isModuleActive('SCORM'))
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            function updateScormReport(key, value) {
                @if (!isset($lesson->completed->status))

                var course_id = "{{ $course->id }}";
                var lesson_id = "{{ $lesson->id }}";
                $.ajax({
                    type: 'POST',
                    url: '{{ route('scorm.report.store') }}',
                    data: {
                        course_id: course_id,
                        lesson_id: lesson_id,
                        key: key,
                        value: value
                    },
                    success: function (data) {
                        console.log(data);
                    }
                });
                @endif


            }
            @endif
        </script>
    @endif
@endpush
