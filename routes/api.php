<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


Auth::routes(['verify' => true]);

Route::group(['namespace' => 'Api'], function () {
});


Route::group([
    'namespace' => 'Api'
], function () {
    Route::post('login', 'AuthController@login');
    Route::post('send-2fa', 'AuthController@sendTFA')->middleware('auth:api');
    Route::post('check-2fa', 'AuthController@checkTFA')->middleware('auth:api');
    Route::post('auto-verify-2fa', 'AuthController@autoVerify')->middleware('auth:api');
    Route::post('social-login', 'AuthController@socialLogin');
    Route::post('signup', 'AuthController@signup');
    Route::post('send-otp', 'AuthController@sendOtp');
    Route::post('reset', 'AuthController@resetWithOtp');
    Route::get('get-lang', 'AuthController@getLang');
//new
    Route::post('resend-email', 'AuthController@resendEmail');

    //CourseApiController
    Route::get('/get-all-courses', 'CourseApiController@getAllCourses');
    Route::get('/get-all-classes', 'CourseApiController@getAllClasses');
    Route::get('/get-all-quizzes', 'CourseApiController@getAllQuizzes');
    Route::get('/get-popular-courses', 'CourseApiController@getPopularCourses');
    Route::get('/get-popular-classes', 'CourseApiController@getPopularClasses');
    Route::get('/get-course-details/{id}', 'CourseApiController@getCourseDetails');
    Route::get('/get-class-details/{id}', 'CourseApiController@getClassDetails');
    Route::get('/get-quiz-details/{id}', 'CourseApiController@getQuizDetails');
    Route::get('/get-lesson-quiz-details/{id}', 'CourseApiController@getLessonQuizDetails');
    Route::get('/top-categories', 'CourseApiController@topCategories');
    Route::get('/search-course', 'CourseApiController@searchCourse');
    Route::get('/filter-course', 'CourseApiController@filterCourse');


    Route::get('/payment-gateways', 'WebsiteApiController@paymentGateways');
    Route::get('/sliders', 'WebsiteApiController@sliders');
    Route::get('categories', 'CourseApiController@categories');
    Route::get('sub-categories/{category_id}', 'CourseApiController@subCategories');
    Route::get('levels', 'CourseApiController@levels');
    Route::get('languages', 'CourseApiController@languages');
    Route::get('mobile-menu', 'MobileMenuController@mobileMenus');

    Route::get('settings', 'WebsiteApiController@settings');


    Route::get('get-institutes', 'WebsiteApiController@getInstitute');


    Route::group([
        'middleware' => ['auth:api', 'verified', '2faApi']
    ], function () {
        //with login routes

        Route::post('set-fcm-token', 'AuthController@setFcmToken');

        Route::post('set-lang', 'AuthController@setLang');

        Route::any('lesson-complete', 'WebsiteApiController@lessonComplete')->name('lesson.complete');


        Route::get('/get-bbb-start-url/{meeting_id}/{user_name}', 'WebsiteApiController@getBbbMeetingUrl');

        Route::get('/cart-list', 'WebsiteApiController@cartList');
        Route::get('/add-to-cart/{id}', 'WebsiteApiController@addToCart');
        Route::get('/remove-to-cart/{id}', 'WebsiteApiController@removeCart');
        Route::post('/apply-coupon', 'WebsiteApiController@applyCoupon');

        //AuthController
        Route::get('logout', 'AuthController@logout');
        Route::get('user', 'AuthController@user');
        Route::post('change-password', 'AuthController@changePassword');
        Route::post('account-delete', 'AuthController@accountDelete');
        Route::post('/update-profile', 'WebsiteApiController@updateProfile');
        Route::post('logout-device', 'AuthController@logOutDevice');


        //WebsiteApiController

        Route::get('/countries', 'WebsiteApiController@countries');
        Route::get('/states/{country_id}', 'WebsiteApiController@states');
        Route::get('/cities/{state_id}', 'WebsiteApiController@cities');
        Route::get('/my-courses', 'WebsiteApiController@myCourses');

        Route::get('/payment-methods', 'WebsiteApiController@paymentMethods');

        Route::post('/make-order', 'WebsiteApiController@makeOrder');
        Route::post('/make-order-ssl', 'WebsiteApiController@makeOrderForSSL');
        Route::post('/make-payment/{gateWayName}', 'WebsiteApiController@payWithGateWay');
        Route::get('/my-billing-address', 'WebsiteApiController@myBilling');

        Route::post('paytm-order-generate', 'WebsiteApiController@paytmOrderGenerate');
        Route::post('paytm-order-verify', 'WebsiteApiController@paytmOrderVerify');


        //quiz route





        Route::delete('/blog-comments/{id}', 'BlogController@blogCommentDelete');

    });
});
