<?php

use App\Http\Controllers\Api\V2\Admin\DashboardController;
use App\Http\Controllers\Api\V2\Assignment\AssignmentController;
use App\Http\Controllers\Api\V2\AuthController;
use App\Http\Controllers\Api\V2\Communication\ConversationController;
use App\Http\Controllers\Api\V2\Course\ChapterController;
use App\Http\Controllers\Api\V2\Course\CourseController;
use App\Http\Controllers\Api\V2\Course\ExerciseController;
use App\Http\Controllers\Api\V2\Course\LessonController;
use App\Http\Controllers\Api\V2\Course\PricePlanController;
use App\Http\Controllers\Api\V2\Course\QuizController as CourseQuizController;
use App\Http\Controllers\Api\V2\GeneralSetting\GeneralSettingController;
use App\Http\Controllers\Api\V2\LanguageController;
use App\Http\Controllers\Api\V2\Payment\PaymentController;
use App\Http\Controllers\Api\V2\Quiz\QuizController;
use App\Http\Controllers\Api\V2\RouteController;
use App\Http\Controllers\Api\V2\StudentController;
use App\Http\Controllers\Api\V2\User\BasicInformationController;
use App\Http\Controllers\Api\V2\UserNotificationController;
use App\Http\Controllers\Api\V2\VirtualClass\VirtualClassController;
use App\Http\Controllers\Api\V2\VirtualClass\ZoomController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes(['verify' => true]);

Route::group([
    'namespace' => 'Api/V2'
], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'signup']);
    Route::post('verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('forgot-password', [AuthController::class, 'sendOtp']);
    Route::post('reset-otp-password', [AuthController::class, 'resetWithOtp']);
    Route::any('social-login', [AuthController::class, 'socialLogin']);


    // Route::get('test', function () {
    //     return response()->json('ok');
    // });
    //    Route::post('social-login', 'AuthController@socialLogin')->middleware('demo');
    //    Route::post('signup', 'AuthController@signup')->middleware('demo');
    //    Route::post('send-otp', 'AuthController@sendOtp');
    //    Route::post('reset', 'AuthController@resetWithOtp');
    //    Route::get('get-lang', 'AuthController@getLang');
});

Route::middleware(['auth:api', 'verified'])->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::any('logout', 'logout');
        Route::get('user-detail', 'userDetail');
        Route::post('set-password', 'changePassword');
        Route::post('remove-self-account', 'deleteSelfAccount');
        Route::any('set-fcm-token', 'setFcmToken');
    });

    Route::controller(UserNotificationController::class)->group(function () {
        Route::get('notifications', 'notificationList');
        Route::any('NotificationMakeAllRead', 'markAllRead');
    });

    Route::controller(CourseController::class)->group(function () {
        Route::get('auth-courses', 'courses');
        Route::post('update-course', 'update');
        Route::get('course-category-list', 'categories');
        Route::get('course-category-detail', 'categoryDetail');
        Route::post('course-category-store', 'categoryStore');
        Route::get('course-subcategory-list', 'subcategories');
        Route::post('course-store', 'storeCourse');
        Route::get('course-levels', 'levels');
        Route::any('change-course-chapter-position', 'chapterRearrange');
        Route::post('change-course-status', 'changeStatus');
        Route::get('course-certificates', 'certificateList');
        Route::post('course-assign-certificate', 'assignCertificate');
        Route::any('change-course-category-status', 'changeCategoryStatus');
        Route::any('course-category-delete', 'courseCategoryDelete');
        Route::post('store-course-level', 'storeCourseLevel');
        Route::post('update-course-level', 'updateCourseLevel');
        Route::any('update-course-level-status', 'changeCourseLevelStatus');
        Route::any('delete-course-level', 'deleteCourseLevel');
        Route::any('delete-course', 'courseDelete');
        Route::post('update-course-category', 'categoryUpdate');
    });

    Route::controller(ExerciseController::class)->group(function () {
        Route::post('course-exercise-store', 'store');
        Route::post('course-exercise-update', 'update');
        Route::any('course-exercise-delete', 'delete');
    });

    Route::controller(PricePlanController::class)->group(function () {
        Route::post('store-course-price-plan', 'storePlan');
        Route::post('update-course-price-plan', 'updatePlan');
        Route::any('delete-course-price-plan', 'deletePlan');
    });

    Route::controller(ChapterController::class)->group(function () {
        Route::post('update-course-chapter', 'update');
        Route::post('create-course-chapter', 'create');
        Route::post('delete-course-chapter', 'delete');
        Route::any('get_chapter_contents', 'contents');
        Route::any('rearrange_chapter_contents', 'rearrangeContents');
        // Route::get('course-chapters', 'chapters');
    });

    Route::controller(LessonController::class)->group(function () {
        Route::post('create-chapter-lesson', 'addLesson');
        Route::post('update-chapter-lesson', 'updateLesson');
        Route::get('lesson-hosts', 'hosts');
        Route::get('lesson-privacies', 'privacies');
        Route::post('delete-lesson', 'deleteLesson');
        Route::get('lesson-detail', 'lessonDetail');
    });

    Route::controller(CourseQuizController::class)->group(function () {
        Route::post('create-course-quiz', 'storeCourseQuiz');
        Route::post('create-quiz-question', 'storeQuestion');
        Route::get('quiz-group-list', 'groupList');
        Route::get('question-level-list', 'questionLevels');
        Route::get('question-type-list', 'questionTypes');
        Route::post('delete-chapter-quiz', 'deleteLesson');
        Route::get('quiz-question-list', 'questionList');
        Route::get('quiz-question-detail', 'questionDetail');
        Route::post('quiz-question-update', 'updateQuestion');
        Route::get('course-quiz-detail', 'quizDetail');
        Route::post('update-course-quiz', 'courseQuizUpdate');
        Route::post('store-course-quiz-group', 'storeQuestionGroup');
        Route::post('update-course-quiz-group', 'updateQuestionGroup');
        Route::any('rearrange-course-quiz-group', 'orderQuestionGroup');
        Route::any('destroy-course-quiz-group', 'destroyQuestionGroup');
        Route::post('question-level-store', 'storeQuestionLevel');
        Route::post('question-level-update', 'updateQuestionLevel');
        Route::any('question-level-status-update', 'updateQuestionLevelStatus');
        Route::any('question-level-delete', 'deleteQuestionLevel');
        Route::post('question-update', 'updateQuizQuestion');
    });

    Route::controller(QuizController::class)->group(function () {
        Route::get('quiz-list', 'quizList');
        Route::get('question-list', 'questions');
        Route::any('change-quiz-status', 'updateQuizStatus');
        Route::any('delete-quiz', 'deleteQuiz');
        Route::any('delete-question', 'deleteQuestion');
        Route::get('question-bank-detail', 'questionBankDetail');
    });

    Route::controller(AssignmentController::class)->group(function () {
        Route::post('create-chapter-assignment', 'store');
        Route::post('update-chapter-assignment', 'assignmentUpdate');
        Route::get('chapter-assignment-list', 'assignmentList');
        Route::get('chapter-assignment-detail', 'assignmentDetail');
        Route::post('delete-chapter-assignment', 'deleteChapterAssignment');
    });

    Route::prefix('admin')->group(function () {
        Route::get('student-list', [StudentController::class, 'studentList']);
        Route::post('change-student-status', [StudentController::class, 'changeStudentStatus']);
        Route::get('student-detail', [StudentController::class, 'studentDetail']);
        Route::get('dashboard', [DashboardController::class, 'dashboard']);
    });

    Route::prefix('conversation')->group(function () {
        Route::controller(ConversationController::class)->group(function () {
            Route::get('list', 'list');
            Route::get('contact-list', 'contactList');
            Route::get('message-list', 'messages');
            Route::any('submit-message', 'storeMessage');
        });
    });

    Route::controller(VirtualClassController::class)->group(function () {
        Route::post('create-virtual-class', 'store');
        Route::get('virtual-class-detail', 'classDetail');
        Route::get('virtual-class-schedules', 'classSchedules');
        Route::get('instructor-list', 'instructors');
        Route::get('certificate-types', 'certificateTypes');
        Route::get('virtual-classes', 'classList');
        Route::any('change-virtual-class-status', 'changeStatus');
        Route::any('delete-virtual-class', 'deleteClass');
        Route::post('update-virtual-class', 'updateClass');
        Route::any('delete-virtual-class-schedule', 'deleteSchedule');
        Route::post('store-virtual-class-price-plan', 'addPricePlan');
        Route::any('delete-virtual-class-price-plan', 'deletePricePlan');
        Route::post('update-virtual-class-price-plan', 'updatePricePlan');
    });

    Route::controller(ZoomController::class)->group(function () {
        Route::prefix('zoom')->group(function () {
            Route::post('configure', 'configure');
            Route::post('settings', 'settings');
            Route::get('settings', 'getConfigSetting');
            Route::get('approval-type-list', 'approvelTypes');
            Route::get('auto-recording-list', 'autoRecordings');
            Route::get('audio-option-list', 'audios');
            Route::get('package-list', 'packages');
        });
    });

    Route::group([
        'prefix'        => 'payment',
        'controller'    => PaymentController::class
    ], function () {
        Route::get('total-earning', 'totalEarning');
        Route::get('payment-list', 'paymentList');
        Route::get('payment-methods', 'paymentMethods');
        Route::post('set-payout', 'savePayout');
    });

    Route::controller(LanguageController::class)->group(function () {
        Route::any('set-lang', 'setLanguage');
        Route::get('get-lang', 'getLang');
        Route::get('language-list', 'languages')->withoutMiddleware(['auth:api', 'verified']);
    });

    Route::controller(BasicInformationController::class)->group(function () {
        Route::post('user-update-basicinfo', 'basicInfoUpdate');
        Route::post('user-update-about', 'aboutUpdate');
        Route::post('user-education-store', 'educationStore');
        Route::post('user-education-update', 'educationUpdate');
        Route::any('user-education-delete', 'educationDestroy');
        Route::post('user-experience-store', 'experienceStore');
        Route::post('user-experience-update', 'experienceUpdate');
        Route::any('user-experience-delete', 'experienceDestroy');
        Route::post('user-skill-update', 'skillStore');
        Route::post('user-extrainfo-update', 'extraInfoUpdate');
        Route::post('user-document-update', 'documentUpdate');
        Route::post('user-socialinfo-update', 'socialInfoUpdate');
    });
});

Route::controller(GeneralSettingController::class)->group(function () {
    Route::get('settings', 'default');
    Route::middleware(['auth:api', 'verified'])->group(function () {
        Route::get('currency-list', 'currencies');
        Route::get('timezone-list', 'timezones');
        Route::get('country-list', 'countries');
        Route::get('state-list', 'states');
        Route::get('city-list', 'cities');
    });
});

Route::controller(CourseController::class)->group(function () {
    Route::get('courses', 'courses');
    Route::get('course-detail', 'detail');
});

Route::controller(ChapterController::class)->group(function () {
    Route::get('course-chapters', 'chapters');
});

Route::controller(LessonController::class)->group(function () {
    Route::get('lessons', 'lessons');
});

Route::any('/{notFound}', [RouteController::class, 'fallback']);
Route::fallback([RouteController::class, 'fallback']);
