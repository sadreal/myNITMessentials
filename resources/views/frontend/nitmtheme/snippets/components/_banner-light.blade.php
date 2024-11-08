<div data-type="component-text"
     data-preview="{{ !function_exists('themeAsset') ? '' : themeAsset('img/snippets/preview/banner/banner-v5.jpg') }}"
     data-aoraeditor-title="Banner Light" data-aoraeditor-categories="Home Page;Banner">

    <style>
        .banner-area {
            position: relative;
            z-index: 1;
            padding-top: 140px;
            padding-bottom: 90px;
            display: flex;
            align-items: center;
        }

        @media only screen and (min-width: 1440px) and (max-width: 1580px) {
            .banner-area {
                padding-bottom: 0px;
            }
        }

        @media only screen and (min-width: 1280px) and (max-width: 1439px) {
            .banner-area {
                --section-sepreate: 110px;
                padding-bottom: 50px;
            }
        }

        @media only screen and (min-width: 992px) and (max-width: 1279px) {
            .banner-area {
                padding-top: 100px;
                padding-bottom: 0px;
                background-position: 80%;
            }
        }

        @media only screen and (min-width: 768px) and (max-width: 991px) {
            .banner-area {
                padding-top: 80px;
                padding-bottom: 0;
                background-size: 80%;
            }
        }

        @media only screen and (max-width: 767px) {
            .banner-area {
                padding-top: 60px;
                padding-bottom: 0;
                background: none;
            }
        }

        @media only screen and (max-width: 479px) {
            .banner-area {
                padding-top: 40px;
                padding-bottom: 0;
            }
        }

        .banner-img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            text-align: right;
            z-index: -1;

        }

        html[dir=rtl] .banner-img {
            transform: scaleX(-1)
        }

        .banner-img img {
            width: auto !important;
            height: 100% !important;
            object-fit: contain;
        }

        .banner-text h1 {
            font-size: 64px;
            line-height: 1.25;
            margin-bottom: 20px;
            color: var(--system_secendory_color) !important;
        }

        @media only screen and (min-width: 1280px) and (max-width: 1439px) {
            .banner-text h1 {
                font-size: 54px;
            }
        }

        @media only screen and (min-width: 992px) and (max-width: 1279px) {
            .banner-text h1 {
                font-size: 50px;
            }
        }

        @media only screen and (min-width: 768px) and (max-width: 991px) {
            .banner-text h1 {
                font-size: 44px;
            }
        }

        @media only screen and (max-width: 767px) {
            .banner-text h1 {
                font-size: 38px;
            }
        }

        @media only screen and (min-width: 768px) and (max-width: 991px) {
            .banner-text h1 {
                margin-bottom: 10px;
            }
        }

        @media only screen and (max-width: 767px) {
            .banner-text h1 {
                margin-bottom: 14px;
            }
        }

        .banner-text p {
            font-size: 18px;
            line-height: 1.6666666667;
            margin-bottom: 60px;
            color: var(--system_paragraph_color) !important;
        }

        @media only screen and (min-width: 1280px) and (max-width: 1439px) {
            .banner-text p {
                margin-bottom: 40px;
            }
        }

        @media only screen and (min-width: 992px) and (max-width: 1279px) {
            .banner-text p {
                padding-right: 0 !important;
                margin-bottom: 40px;
            }

            html[dir=rtl] .banner-text p {
                padding-right: 0 !important;
            }
        }

        @media only screen and (min-width: 768px) and (max-width: 991px) {
            .banner-text p {
                font-size: 16px;
                margin-bottom: 36px;
            }
        }

        @media only screen and (max-width: 767px) {
            .banner-text p {
                font-size: 16px;
                margin-bottom: 24px;
            }
        }

        .banner-text .theme-btn {
            --btn-padding-y: 16px;
        }

        @media only screen and (min-width: 768px) and (max-width: 991px) {
            .banner-text .theme-btn {
                --btn-padding-y: 12px;
            }
        }

        @media only screen and (max-width: 767px) {
            .banner-text .theme-btn {
                --btn-padding-y: 10px;
                --btn-padding-x: 24px;
            }
        }

        .banner-free {
            padding: 20px;
            min-width: 250px;
            background: rgba(0, 0, 0, 0.1);
            box-shadow: inset 0px 0px 3px 1px rgba(214, 214, 214, 0.2), inset 0px 0px 3px rgba(255, 255, 255, 0.2);
            -webkit-backdrop-filter: blur(3.5px);
            backdrop-filter: blur(3.5px);
            border-radius: 12px;
            --icon: 60px;
            position: absolute;
            right: 22px;
            top: 300px;
            display: none !important;
        }

        html[dir=rtl] .banner-free {
            right: auto;
            left: 22px;
        }

        @media only screen and (min-width: 1440px) and (max-width: 1580px) {
            .banner-free {
                min-width: 220px;
                top: 260px;
            }
        }

        @media only screen and (min-width: 1280px) and (max-width: 1439px) {
            .banner-free {
                padding: 15px;
                min-width: 200px;
                --icon: 50px;
                right: 10px;
                top: 240px;
            }

            html[dir=rtl] .banner-free {
                right: auto;
                left: 10px;
            }
        }

        @media only screen and (min-width: 992px) and (max-width: 1279px) {
            .banner-free {
                padding: 12px;
                min-width: 160px;
                --icon: 40px;
                right: 10px;
                top: 200px;
            }

            html[dir=rtl] .banner-free {
                right: auto;
                left: 10px;
            }
        }

        .banner-free .icon {
            width: var(--icon);
            flex: 0 0 auto;
        }

        .banner-free .icon img {
            width: 100%;
        }

        .banner-free .content {
            padding-left: 20px;
            width: calc(100% - var(--icon));
            flex: 0 0 auto;
        }

        html[dir=rtl] .banner-free .content {
            padding-left: 0;
            padding-right: 20px;
        }

        @media only screen and (min-width: 1280px) and (max-width: 1439px) {
            .banner-free .content {
                padding-left: 14px;
            }

            html[dir=rtl] .banner-free .content {
                padding-left: 0;
                padding-right: 14px;
            }
        }

        @media only screen and (min-width: 992px) and (max-width: 1279px) {
            .banner-free .content {
                padding-left: 10px;
            }

            html[dir=rtl] .banner-free .content {
                padding-left: 0;
                padding-right: 10px;
            }
        }

        .banner-free .content h4 {
            font-size: 28px;
            line-height: 1.5;
            line-height: 1;
        }

        @media only screen and (min-width: 992px) and (max-width: 1279px) {
            .banner-free .content h4 {
                font-size: 24px;
            }
        }

        @media only screen and (min-width: 768px) and (max-width: 991px) {
            .banner-free .content h4 {
                font-size: 24px;
            }
        }

        @media only screen and (max-width: 767px) {
            .banner-free .content h4 {
                font-size: 22px;
            }
        }

        @media only screen and (min-width: 992px) and (max-width: 1279px) {
            .banner-free .content h4 {
                font-size: 20px;
            }
        }

        .banner-free .content span {
            font-size: 18px;
            line-height: 1;
        }

        @media only screen and (min-width: 992px) and (max-width: 1279px) {
            .banner-free .content span {
                font-size: 16px;
            }
        }

        .banner-star {
            background: rgba(0, 0, 0, 0.3);
            box-shadow: inset 0px 0px 1.5px 1px rgba(214, 214, 214, 0.4), inset 0px 0px 1.38667px rgba(255, 255, 255, 0.4);
            -webkit-backdrop-filter: blur(5px);
            backdrop-filter: blur(5px);
            border-radius: 12px;
            min-width: 150px;
            text-align: center;
            padding: 10px;
            position: absolute;
            right: 345px;
            top: 585px;
            display: none !important;
        }

        html[dir=rtl] .banner-star {
            right: auto;
            left: 345px;
        }

        @media only screen and (min-width: 1440px) and (max-width: 1580px) {
            .banner-star {
                right: 290px;
                top: 505px;
            }

            html[dir=rtl] .banner-star {
                right: auto;
                left: 290px;
            }
        }

        @media only screen and (min-width: 1280px) and (max-width: 1439px) {
            .banner-star {
                right: 245px;
                top: 435px;
            }

            html[dir=rtl] .banner-star {
                right: auto;
                left: 245px;
            }
        }

        @media only screen and (min-width: 992px) and (max-width: 1279px) {
            .banner-star {
                right: 145px;
                top: 395px;
            }

            html[dir=rtl] .banner-star {
                right: auto;
                left: 145px;
            }
        }

        .banner-star .rating {
            font-size: 16px;
            line-height: 1.5;
        }

        .banner-star .rating i:not(:last-child) {
            margin-right: 6px;
        }

        html[dir=rtl] .banner-star .rating i:not(:last-child) {
            margin-right: 0;
            margin-left: 6px;
        }

    </style>

    <form action="{{ route('search') }}">
        <div class="banner-area">
            <div class="banner-img d-none d-md-block">
                <img src="{{ asset('public/frontend/nitmtheme/img/banner/banner-v6.png')}}" alt="">
            </div>
            <div class="banner-free d-none d-lg-flex align-items-center">
                <div class="icon">
                    <img src="{{ themeAsset('img/shape/laptop-star.png') }}" alt="">
                </div>
                <div class="content">
                    <h4 class="fw-bold text-primary mb-0">7100+</h4>
                    <span>Free Courses</span>
                </div>
            </div>
            <div class="banner-star d-none d-lg-block">
                <div class="rating">
                    <div class="fa fa-star"></div>
                    <div class="fa fa-star"></div>
                    <div class="fa fa-star"></div>
                    <div class="fa fa-star"></div>
                    <div class="fa fa-star-half-alt"></div>
                </div>
                <span class="fs-14 lh-1 text-white">5300+ Rating</span>
            </div>
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-xl-6 col-lg-6 col-md-6">
                        <div class="banner-text">
                            <h1 class="text-white">{{ @$homeContent->slider_title }}</h1>
                            <p class="pe-0 pe-xl-5 text-white">{{ @$homeContent->slider_text }}</p>
                            <a href="{{route('courses')}}" class="theme-btn text-capitalize">View All Courses</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
