<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes(['verify' => true]);


Route::get('send-password-reset-link', 'Auth\ForgotPasswordController@SendPasswordResetLink')->name('SendPasswordResetLink');
Route::get('reset-password', 'Auth\ForgotPasswordController@ResetPassword')->name('ResetPassword');
Route::get('register', 'Auth\RegisterController@RegisterForm')->name('register');
Route::get('saas-signup', 'Auth\RegisterController@LmsRegisterForm')->name('lms_register');

Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout')->name('logout');
Route::post('/resend', '\App\Http\Controllers\Auth\VerificationController@resend_mail')->name('verification_mail_resend');
Route::get('auto-login/{key}', '\App\Http\Controllers\Auth\LoginController@autologin')->name('auto.login');


Route::get('/test', 'Frontend\FrontendHomeController@test');
Route::get('/update-version', 'Frontend\FrontendHomeController@version');

Route::group(['namespace' => 'Frontend'], function () {
    Route::get('/', 'FrontendHomeController@index')->name('frontendHomePage');

    Route::get('/get-courses-by-category/{category_id}', 'EdumeFrontendThemeController@getCourseByCategory')->name('getCourseByCategory');
    //wetech theme controller
    Route::get('/wetech/{route_name}', 'WeTechFrontendThemeController@route')->name('weTechController');

    Route::get('/offline', 'WebsiteController@offline')->name('offline');
    Route::get('/app-mode', 'WebsiteController@onlyAppMode')->name('onlyAppMode');


    Route::get('free-course', 'CourseController@freeCourses')->name('freeCourses');






    Route::get('search', 'WebsiteController@search')->name('search');
    Route::get('category/{id}/{name}', 'WebsiteController@categoryCourse')->name('categoryCourse');
    Route::get('sub_category/{id}/{slug}', 'WebsiteController@subCategoryCourse')->name('subCategory.course');


   
    Route::get('deposit', 'StudentController@deposit')->name('deposit');
    Route::post('deposit', 'StudentController@deposit')->name('depositSelectOption');
    Route::get('logged-in/devices', 'StudentController@loggedInDevices')->name('logged.in.devices');
    Route::get('invoice/{id}', 'StudentController@Invoice')->name('invoice');
    Route::get('my-purchase-order-detail/{id}', 'StudentController@my_purchase_order_detail')->name('my_purchase_order_detail');
    Route::get('my-virtual-file-download/{id}', 'StudentController@my_virtual_file_download')->name('my_virtual_file_download');
    Route::get('my-virtual-file/{slug}', 'StudentController@downloadVirtualFile')->name('downloadVirtualFile');
    Route::get('/make-refund-request/{id}', 'StudentController@make_refund_request')->name('refund.make_request');
    Route::post('/make-refund-request-store', 'StudentController@store_refund_request')->name('refund.refund_make_request_store');
    Route::get('/my-purchase-order-pdf/{id}', 'StudentController@my_purchase_order_pdf')->name('frontend.my_purchase_order_pdf');



  Route::group(['middleware' => ['student']], function () {
    Route::get('my-notification-setup', 'NotificationController@myNotificationSetup')->name('myNotificationSetup');
    Route::get('my-notifications', 'NotificationController@myNotification')->name('myNotification');
    Route::get('my-noticeboard', 'NotificationController@myNoticeboard')->name('myNoticeboard');
    Route::get('show-noticeboard/{id}', 'NotificationController@showNoticeboard')->name('showNoticeboard');
});


//in this controller we can use for place order
Route::group(['prefix' => 'order', 'middleware' => ['auth']], function () {

    Route::post('submit', 'PaymentController@makePlaceOrder')->name('makePlaceOrder');
    Route::get('/payment', 'PaymentController@payment')->name('orderPayment');
    Route::post('/paymentSubmit', 'PaymentController@paymentSubmit')->name('paymentSubmit');
    //paypal url
    Route::get('paypal/success', 'PaymentController@paypalSuccess')->name('paypalSuccess');
    Route::get('paypal/failed', 'PaymentController@paypalFailed')->name('paypalFailed');
});
//deposit
Route::group(['prefix' => 'deposit', 'middleware' => ['auth']], function () {

    Route::post('submit', 'DepositController@depositSubmit')->name('depositSubmit');
    Route::get('paypalDepositSuccess', 'DepositController@paypalDepositSuccess')->name('paypalDepositSuccess');
    Route::get('paypalDepositFailed', 'DepositController@paypalDepositFailed')->name('paypalDepositFailed');

});

Route::group(['prefix' => 'subscription', 'middleware' => ['auth']], function () {
    Route::post('payment', 'SubscriptionPaymentController@payment')->name('subscriptionPayment');
    Route::post('submit', 'SubscriptionPaymentController@subscriptionSubmit')->name('subscriptionSubmit');
    Route::get('paypalSubscriptionSuccess', 'SubscriptionPaymentController@paypalSubscriptionSuccess')->name('paypalSubscriptionSuccess');
    Route::get('paypalSubscriptionFailed', 'SubscriptionPaymentController@paypalSubscriptionFailed')->name('paypalSubscriptionFailed');

});




//Admin Routes Here
Route::group(['namespace' => 'Admin', 'prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['auth', 'admin']], function () {


    Route::post('/get-user-data/{id}', 'AdminController@getUserDate')->name('getUserDate');
    Route::post('remove-image', 'AdminController@removeImageByAjax')->name('removeImageByAjax');


    Route::get('/reveune-list', 'AdminController@reveuneList')->name('reveuneList')->middleware('RoutePermissionCheck:admin.reveuneList');
    Route::get('/reveuneListInstructor', 'AdminController@reveuneListInstructor')->name('reveuneListInstructor')->middleware('RoutePermissionCheck:admin.reveuneListInstructor');
//
    Route::get('/enrol-list', 'AdminController@enrollLogs')->name('enrollLogs')->middleware('RoutePermissionCheck:admin.enrollLogs');
    Route::get('/cancel-list', 'AdminController@cancelLogs')->name('cancelLogs')->middleware('RoutePermissionCheck:admin.enrollLogs');

    Route::post('/enrol-delete', 'AdminController@enrollDelete')->name('enrollDelete')->middleware('RoutePermissionCheck:admin.enrollLogs');

    Route::post('/enrol-generate-certificate', 'AdminController@generateCertificate')->name('generateCertificate')->middleware('RoutePermissionCheck:admin.enrollLogs');
    Route::post('/enrol-remove-certificate', 'AdminController@removeCertificate')->name('removeCertificate')->middleware('RoutePermissionCheck:admin.enrollLogs');

 ;
});
Route::get('get_preview_modal/{id}', 'AjaxController@get_preview_modal')->name('get_preview_modal');
Route::get('ajaxGetSubCategoryList', 'AjaxController@ajaxGetSubCategoryList')->name('ajaxGetSubCategoryList');
Route::get('ajaxGetCourseList', 'AjaxController@ajaxGetCourseList')->name('ajaxGetCourseList');
Route::get('ajaxGetQuizList', 'AjaxController@ajaxGetQuizList')->name('ajaxGetQuizList');
Route::get('update-activity', 'AjaxController@updateActivity')->name('updateActivity');
Route::get('get_preview_modal/{id}', 'AjaxController@get_preview_modal')->name('get_preview_modal');
Route::get('get_cart_price', 'AjaxController@get_cart_price')->name('get_cart_price');

Route::post('summer-note-file-upload', 'UploadFileController@upload_image')->name('summerNoteFileUpload');



