<input type="hidden" class="class_route" name="class_route" value="{{validRouteUrl('store.products')}}">
@if (isset($result))
    <div class="row">

        <div class="col-12 mb-30">
            <div class="course-title d-flex flex-wrap align-items-center justify-content-between">
                <h5 class="mb-0">{{ __('frontend.Showing') }} {{ $result->firstItem() }}–{{ $result->lastItem() }} of
                    {{ $result->total() }} {{ __('product.Results') }}</h5>

                <div class="d-inline-flex align-items-center gap-2">
                    <ul class="nav nav-pills store_view mb-0 gap-2" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#store_product_grid"
                                    type="button" role="tab" aria-controls="pills-grid" aria-selected="true">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M6.38889 11.1111C7.7696 11.1111 8.88889 12.2304 8.88889 13.6111V17.5C8.88889 18.8807 7.7696 20 6.38889 20H2.5C1.11929 20 0 18.8807 0 17.5V13.6111C0 12.2304 1.11929 11.1111 2.5 11.1111H6.38889ZM17.5 11.1111C18.8807 11.1111 20 12.2304 20 13.6111V17.5C20 18.8807 18.8807 20 17.5 20H13.6111C12.2304 20 11.1111 18.8807 11.1111 17.5V13.6111C11.1111 12.2304 12.2304 11.1111 13.6111 11.1111H17.5ZM6.38889 12.7778H2.5C2.03977 12.7778 1.66667 13.1509 1.66667 13.6111V17.5C1.66667 17.9602 2.03977 18.3333 2.5 18.3333H6.38889C6.84912 18.3333 7.22222 17.9602 7.22222 17.5V13.6111C7.22222 13.1509 6.84912 12.7778 6.38889 12.7778ZM17.5 12.7778H13.6111C13.1509 12.7778 12.7778 13.1509 12.7778 13.6111V17.5C12.7778 17.9602 13.1509 18.3333 13.6111 18.3333H17.5C17.9602 18.3333 18.3333 17.9602 18.3333 17.5V13.6111C18.3333 13.1509 17.9602 12.7778 17.5 12.7778ZM6.38889 0C7.7696 0 8.88889 1.11929 8.88889 2.5V6.38889C8.88889 7.7696 7.7696 8.88889 6.38889 8.88889H2.5C1.11929 8.88889 0 7.7696 0 6.38889V2.5C0 1.11929 1.11929 0 2.5 0H6.38889ZM17.5 0C18.8807 0 20 1.11929 20 2.5V6.38889C20 7.7696 18.8807 8.88889 17.5 8.88889H13.6111C12.2304 8.88889 11.1111 7.7696 11.1111 6.38889V2.5C11.1111 1.11929 12.2304 0 13.6111 0H17.5ZM6.38889 1.66667H2.5C2.03977 1.66667 1.66667 2.03977 1.66667 2.5V6.38889C1.66667 6.84912 2.03977 7.22222 2.5 7.22222H6.38889C6.84912 7.22222 7.22222 6.84912 7.22222 6.38889V2.5C7.22222 2.03977 6.84912 1.66667 6.38889 1.66667ZM17.5 1.66667H13.6111C13.1509 1.66667 12.7778 2.03977 12.7778 2.5V6.38889C12.7778 6.84912 13.1509 7.22222 13.6111 7.22222H17.5C17.9602 7.22222 18.3333 6.84912 18.3333 6.38889V2.5C18.3333 2.03977 17.9602 1.66667 17.5 1.66667Z"
                                        fill="white"/>
                                </svg>

                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#store_product_list"
                                    type="button" role="tab" aria-controls="pills-list" aria-selected="false">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M4.24726 14.0001C5.21361 14.0001 5.99701 14.7835 5.99701 15.7498V18.2474C5.99701 19.2137 5.21361 19.9971 4.24726 19.9971H1.74975C0.783386 19.9971 0 19.2137 0 18.2474V15.7498C0 14.7835 0.783386 14.0001 1.74975 14.0001H4.24726ZM4.24726 15.4999H1.74975C1.6117 15.4999 1.49978 15.6118 1.49978 15.7498V18.2474C1.49978 18.3854 1.6117 18.4973 1.74975 18.4973H4.24726C4.38531 18.4973 4.49723 18.3854 4.49723 18.2474V15.7498C4.49723 15.6118 4.38531 15.4999 4.24726 15.4999ZM7.74697 15.9977H19.2501C19.6642 15.9977 20 16.3334 20 16.7476C20 17.1272 19.7178 17.441 19.3519 17.4907L19.2501 17.4975H7.74697C7.33282 17.4975 6.99708 17.1617 6.99708 16.7476C6.99708 16.3679 7.27919 16.0542 7.64521 16.0045L7.74697 15.9977H19.2501H7.74697ZM4.24726 7.00004C5.21361 7.00004 5.99701 7.78343 5.99701 8.74983V11.2473C5.99701 12.2136 5.21361 12.997 4.24726 12.997H1.74975C0.783386 12.997 0 12.2136 0 11.2473V8.74983C0 7.78343 0.783386 7.00004 1.74975 7.00004H4.24726ZM4.24726 8.49987H1.74975C1.6117 8.49987 1.49978 8.61175 1.49978 8.74983V11.2473C1.49978 11.3854 1.6117 11.4972 1.74975 11.4972H4.24726C4.38531 11.4972 4.49723 11.3854 4.49723 11.2473V8.74983C4.49723 8.61175 4.38531 8.49987 4.24726 8.49987ZM7.74697 8.9987H19.2501C19.6642 8.9987 20 9.33445 20 9.74859C20 10.1282 19.7178 10.442 19.3519 10.4917L19.2501 10.4985H7.74697C7.33282 10.4985 6.99708 10.1627 6.99708 9.74859C6.99708 9.36894 7.27919 9.05519 7.64521 9.00549L7.74697 8.9987H19.2501H7.74697ZM4.24726 0C5.21361 0 5.99701 0.783386 5.99701 1.74975V4.24726C5.99701 5.21361 5.21361 5.99701 4.24726 5.99701H1.74975C0.783386 5.99701 0 5.21361 0 4.24726V1.74975C0 0.783386 0.783386 0 1.74975 0H4.24726ZM4.24726 1.49978H1.74975C1.6117 1.49978 1.49978 1.6117 1.49978 1.74975V4.24726C1.49978 4.38531 1.6117 4.49723 1.74975 4.49723H4.24726C4.38531 4.49723 4.49723 4.38531 4.49723 4.24726V1.74975C4.49723 1.6117 4.38531 1.49978 4.24726 1.49978ZM7.74697 1.99971H19.2501C19.6642 1.99971 20 2.33545 20 2.7496C20 3.12925 19.7178 3.44299 19.3519 3.49264L19.2501 3.49949H7.74697C7.33282 3.49949 6.99708 3.16375 6.99708 2.7496C6.99708 2.36996 7.27919 2.05621 7.64521 2.00656L7.74697 1.99971H19.2501H7.74697Z"
                                        fill="white"/>
                                </svg>

                            </button>
                        </li>
                    </ul>
                    <ul class="d-flex align-items-center flex-wrap mb-2 mb-md-0 ms-auto view-option">

                        <li>
                            <select class="search-hide" id="custom_pagination">
                                <option
                                    value="12" {{ request('custom_pagination') == '12' ? 'selected' : '' }}>{{__('common.Show')}}
                                    12
                                    {{__('common.Items')}}
                                </option>
                                <option
                                    value="20" {{ request('custom_pagination') == '20' ? 'selected' : '' }}>{{__('common.Show')}}
                                    20
                                    {{__('common.Items')}}
                                </option>
                                <option
                                    value="32" {{ request('custom_pagination') == '32' ? 'selected' : '' }}>{{__('common.Show')}}
                                    32
                                    {{__('common.Items')}}
                                </option>
                                <option
                                    value="40" {{ request('custom_pagination') == '40' ? 'selected' : '' }}>{{__('common.Show')}}
                                    40
                                    {{__('common.Items')}}
                                </option>
                            </select>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="store_product_grid" role="tabpanel"
                 aria-labelledby="pills-home-tab" tabindex="0">
                <div class="row">
                    @forelse ($result as $course)
                        <div class="col-xl-4 col-sm-6 d-flex mb-4">
                            <div class="shop-item bg-white w-100">
                                <div class="text-center position-relative">
                                    <div class="shop-item-rating">

                                        @php
                                            $main_stars= $course->total_rating;
                                            if ($main_stars>=4){
                                                $main_stars =5;
                                            }
                                            $stars=intval($main_stars);
                                            $remain =ceil(5-$main_stars);
                                            if($remain>4){
                                                $remain=5;
                                            }

                                        @endphp
                                        @for ($i = 0; $i <  $stars; $i++)
                                            <i class="fas fa-star text-primary"></i>
                                        @endfor
                                        @if ($main_stars>$stars)
                                            <i class="fas fa-star-half text-primary"></i>
                                        @endif
                                        @if($main_stars==0)
                                            @for ($i = 0; $i <  5; $i++)
                                                <i class="far fa-star text-primary"></i>
                                            @endfor
                                        @else
                                            @for ($i = 0; $i <  $remain; $i++)
                                                <i class="far fa-star text-primary"></i>
                                            @endfor
                                        @endif
                                    </div>
                                    <a href="{{ courseDetailsUrl(@$course->id, @$course->type, @$course->slug) }}"
                                       class="shop-item-img d-block">
                                        <img class="img-fluid" src="{{ getCourseImage($course->product?->thumbnail) }}"
                                             alt="">
                                    </a>
                                </div>
                                <div class="shop-item-content">
                                    <span>{{ @$course->product_category->title }}</span>
                                    <h4><a href="{{ courseDetailsUrl(@$course->id, @$course->type, @$course->slug) }}"
                                           class="currentColor">{{ $course->title }}</a>
                                    </h4>
                                    <div class="d-flex align-items-end justify-content-between">
                                        <div>
                                            @if ($course->product?->discount > 0)
                                                <del class="d-block fw-500">

                                                    {{ getPriceFormat(@$course->product->price) }}
                                                </del>

                                            @endif
                                            <strong class="fw-bold d-block">
                                                @php
                                                    if (@$course->product->discount_type == 1) {
                                                        $price = $course->product->price - $course->product?->discount;
                                                    } elseif (@$course->product->discount_type == 2) {
                                                        $price = $course->product->price - ($course->product->price * $course->product?->discount) / 100;
                                                    } else {
                                                        $price = $course->product?->price;
                                                    }
                                                @endphp
                                                {{ getPriceFormat($price) }}
                                            </strong>
                                        </div>
                                        <div>
                                            @if (Auth::check())
                                                @if (!$course->isLoginUserEnrolled)
                                                    <a href="{{route('buyNow',[@$course->id])}}"
                                                       class="theme-btn buyNow"><i
                                                            class="fa fa-shopping-cart"></i>{{ __('common.Buy Now') }}
                                                    </a>
                                                @endif
                                            @else
                                                <a href="{{route('buyNow',[@$course->id])}}"
                                                   class="theme-btn buyNow"><i
                                                        class="fa fa-shopping-cart"></i>{{ __('common.Buy Now') }}</a>
                                            @endif

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    @empty
                        <div class="col-lg-12 mb-4 mt-5">
                            <div class="Nocouse_wizged text-center d-flex align-items-center justify-content-center">
                                <div class="thumb">
                                    <img style="width: 50px"
                                         src="{{ asset('public/frontend/nitmtheme') }}/img/not-found.png"
                                         alt="">
                                </div>
                                <h1>
                                    {{ __('product.No Product Found') }}
                                </h1>
                            </div>
                        </div>
                    @endforelse
                    @if (isset($has_pagination))
                        {{ $result->appends(Request::all())->links(theme('snippets.components._dynamic_pagination')) }}
                    @endif
                </div>
            </div>
            <div class="tab-pane fade" id="store_product_list" role="tabpanel" aria-labelledby="pills-profile-tab"
                 tabindex="0">
                <div class="row">
                    @forelse ($result as $course)
                        <div class="col-xxl-6 d-flex mb-4">
                            <div class="shop-item list_view d-flex gap-3 bg-white w-100">
                                <div class="text-center position-relative h-fit">
                                    <div class="shop-item-rating">

                                        @php
                                            $main_stars= $course->total_rating;
                                            if ($main_stars>=4){
                                                $main_stars =5;
                                            }
                                            $stars=intval($main_stars);
                                            $remain =ceil(5-$main_stars);
                                            if($remain>4){
                                                $remain=5;
                                            }

                                        @endphp
                                        @for ($i = 0; $i <  $stars; $i++)
                                            <i class="fas fa-star text-primary"></i>
                                        @endfor
                                        @if ($main_stars>$stars)
                                            <i class="fas fa-star-half text-primary"></i>
                                        @endif
                                        @if($main_stars==0)
                                            @for ($i = 0; $i <  5; $i++)
                                                <i class="far fa-star text-primary"></i>
                                            @endfor
                                        @else
                                            @for ($i = 0; $i <  $remain; $i++)
                                                <i class="far fa-star text-primary"></i>
                                            @endfor
                                        @endif
                                    </div>
                                    <a href="{{ courseDetailsUrl(@$course->id, @$course->type, @$course->slug) }}"
                                       class="shop-item-img d-block">
                                        <img class="img-fluid thumbnail"
                                             src="{{ getCourseImage($course->product?->thumbnail) }}"
                                             alt="">
                                    </a>
                                </div>
                                <div class="shop-item-content flex-grow-1 d-flex flex-column">
                                    <span>{{ @$course->product_category->title }}</span>
                                    <h4><a href="{{ courseDetailsUrl(@$course->id, @$course->type, @$course->slug) }}"
                                           class="currentColor">{{ $course->title }}</a>
                                    </h4>
                                    <div
                                        class="d-flex align-items-end justify-content-between gap-3 flex-wrap flex-grow-1">
                                        <div>
                                            @if (Auth::check())
                                                @if (!$course->isLoginUserEnrolled)
                                                    <a href="{{route('buyNow',[@$course->id])}}"
                                                       class="theme-btn buyNow"><i
                                                            class="fa fa-shopping-cart"></i>{{ __('common.Buy Now') }}
                                                    </a>
                                                @endif
                                            @else
                                                <a href="{{route('buyNow',[@$course->id])}}"
                                                   class="theme-btn buyNow"><i
                                                        class="fa fa-shopping-cart"></i>{{ __('common.Buy Now') }}</a>
                                            @endif

                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            @if ($course->product?->discount > 0)
                                                <del class="d-block fw-500">

                                                    {{ getPriceFormat(@$course->product->price) }}
                                                </del>

                                            @endif
                                            <strong class="fw-bold d-block">
                                                @php
                                                    if (@$course->product->discount_type == 1) {
                                                        $price = $course->product->price - $course->product?->discount;
                                                    } elseif (@$course->product->discount_type == 2) {
                                                        $price = $course->product->price - ($course->product->price * $course->product?->discount) / 100;
                                                    } else {
                                                        $price = $course->product?->price;
                                                    }
                                                @endphp
                                                {{ getPriceFormat($price) }}
                                            </strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    @empty
                        <div class="col-lg-12 mb-4 mt-5">
                            <div class="Nocouse_wizged text-center d-flex align-items-center justify-content-center">
                                <div class="thumb">
                                    <img style="width: 50px"
                                         src="{{ asset('public/frontend/nitmtheme') }}/img/not-found.png"
                                         alt="">
                                </div>
                                <h1>
                                    {{ __('product.No Product Found') }}
                                </h1>
                            </div>
                        </div>
                    @endforelse
                    @if (isset($has_pagination))
                        {{ $result->appends(Request::all())->links(theme('snippets.components._dynamic_pagination')) }}
                    @endif
                </div>
            </div>
        </div>

    </div>
    <input type="hidden" value="{{asset('/')}}" id="baseUrl">

    <script>
        $(document).ready(function () {
            // select js
            $(".search-hide").select2({
                minimumResultsForSearch: Infinity,
            });
        });

        if ($.isFunction($.fn.lazy)) {
            $('.lazy').lazy();
        }
    </script>
@endif
