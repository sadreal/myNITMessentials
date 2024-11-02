<?php

namespace App\Repositories\Eloquents;

use App\Http\Resources\api\v2\Chapter\ChapterContentListResource;
use App\Http\Resources\api\v2\Chapter\ChapterListResource;
use App\Jobs\SendGeneralEmail;
use App\LessonComplete;
use App\Repositories\Interfaces\ChapterRepositoryInterface;
use Carbon\Carbon;
use Modules\CourseSetting\Entities\Chapter;
use Modules\CourseSetting\Entities\Course;
use Modules\CourseSetting\Entities\Lesson;
use Modules\CourseSetting\Http\Controllers\CourseSettingController;
use Modules\CourseSetting\Http\Controllers\LessonController;

class ChapterRepository implements ChapterRepositoryInterface
{
    public function chapters(object $request): object
    {
        $chapters = Chapter::where('course_id', $request->course_id)->orderBy('position')->get();
        return ChapterListResource::collection($chapters);
    }
    public function update(object $request): void
    {
        $userRole = in_array(auth()->user()->role_id, [1, 2]);

        if ($userRole) {
            $course = Course::where('user_id', auth()->id())->find($request->course_id);
        } else {
            $course = Course::find($request->course_id);
        }

        if ($course) {
            $chapter = Chapter::where('course_id', $course->id)->find($request->chapter_id);
            $chapter->update(['name' => $request->chapter_name]);
            (new CourseSettingController)->updateTotalCountForCourse($course);
        }
    }

    public function create(object $request): void
    {
        if (in_array(auth()->user()->role_id, [1, 2])) {
            $course = Course::when(auth()->user()->role_id == 2, function ($q) {
                $q->where('user_id', auth()->id());
            })->find($request->course_id);
        }

        if ($course) {
            $chapterNo = Chapter::where('course_id', $request->course_id)->count();

            $chapter = Chapter::create([
                'course_id'     => $course->id,
                'name'          => $request->chapter_name,
                'chapter_no'    => ++$chapterNo
            ]);

            if (isset($course->enrollUsers) && !empty($course->enrollUsers)) {
                foreach ($course->enrollUsers as $user) {
                    if (UserMobileNotificationSetup('Course_Chapter_Added', $user) && !empty($user->device_token)) {
                        send_mobile_notification($user, 'Course_Chapter_Added', [
                            'time'      => Carbon::now()->format('d-M-Y, g:i A'),
                            'course'    => $course->title,
                            'chapter'   => $chapter->name
                        ]);
                    }
                    if (UserEmailNotificationSetup('Course_Chapter_Added', $user)) {
                        SendGeneralEmail::dispatch($user, 'Course_Chapter_Added', [
                            'time'      => Carbon::now()->format('d-M-Y, g:i A'),
                            'course'    => $course->title,
                            'chapter'   => $chapter->name
                        ]);
                    }

                    if (UserSmsNotificationSetup('Course_Chapter_Added', $user)) {
                        send_sms_notification($user, $type = 'Course_Chapter_Added', $shortcodes = [
                            'time'      => Carbon::now()->format('d-M-Y, g:i A'),
                            'course'    => $course->title,
                            'chapter'   => $chapter->name
                        ]);
                    }

                    if (UserBrowserNotificationSetup('Course_Chapter_Added', $user)) {
                        send_browser_notification(
                            $user,
                            $type = 'Course_Chapter_Added',
                            $shortcodes = [
                                'time'      => Carbon::now()->format('d-M-Y, g:i A'),
                                'course'    => $course->title,
                                'chapter'   => $chapter->name
                            ],
                            trans('common.View'), //actionText
                            courseDetailsUrl(@$course->id, @$course->type, @$course->slug), //actionUrl
                            'chapter',
                            $course->id
                        );
                    }
                }
            }

            $courseUser = $course->user;
            if (UserMobileNotificationSetup('Course_Chapter_Added', $courseUser) && !empty($courseUser->device_token)) {
                send_mobile_notification($courseUser, 'Course_Chapter_Added', [
                    'time'      => Carbon::now()->format('d-M-Y, g:i A'),
                    'course'    => $course->title,
                    'chapter'   => $chapter->name
                ]);
            }
            if (UserEmailNotificationSetup('Course_Chapter_Added', $courseUser)) {
                SendGeneralEmail::dispatch($courseUser, 'Course_Chapter_Added', [
                    'time'      => Carbon::now()->format('d-M-Y ,g:i A'),
                    'course'    => $course->title,
                    'chapter'   => $chapter->name
                ]);
            }
            if (UserBrowserNotificationSetup('Course_Chapter_Added', $courseUser)) {
                send_browser_notification(
                    $courseUser,
                    $type = 'Course_Chapter_Added',
                    $shortcodes = [
                        'time'      => Carbon::now()->format('d-M-Y ,g:i A'),
                        'course'    => $course->title,
                        'chapter'   => $chapter->name
                    ],
                    trans('common.View'), //actionText
                    courseDetailsUrl(@$course->id, @$course->type, @$course->slug), //actionUrl
                    'chapter',
                    $course->id
                );
            }

            if (UserSmsNotificationSetup('Course_Chapter_Added', $courseUser)) {
                send_sms_notification($courseUser, $type = 'Course_Chapter_Added', $shortcodes = [
                    'time'      => Carbon::now()->format('d-M-Y ,g:i A'),
                    'course'    => $course->title,
                    'chapter'   => $chapter->name
                ]);
            }

            (new CourseSettingController())->updateTotalCountForCourse($course);
        }
    }

    public function delete(object $request): void
    {
        if (auth()->user()->role_id == 2) {
            $course = Course::where('user_id', auth()->user()->id)->find($request->course_id);
        } else {
            $course = Course::find($request->course_id);
        }

        if ($course) {
            $lessons = Lesson::where('chapter_id', $request->chapter_id)->where('course_id', $request->course_id)->get();

            foreach ($lessons as $lesson) {
                $completeLessons = LessonComplete::where('lesson_id', $lesson->id)->get();

                foreach ($completeLessons as $completeLesson) {
                    $completeLesson->delete();
                }

                $lessonController = new LessonController();
                $lessonController->lessonFileDelete($lesson);
                $lesson->delete();
            }

            $chapter = Chapter::find($request->chapter_id);
            $chapter->delete();

            $this->updateTotalCountForCourse($course);
        }
    }

    public function contents(object $request): object
    {
        $contents = Lesson::where('course_id', $request->course_id)->where('chapter_id', $request->chapter_id)->orderBy('position')->get();
        return ChapterContentListResource::collection($contents);
    }

    public function rearrangeContents(object $request): void
    {
        $contents = Lesson::where('course_id', $request->course_id)->where('chapter_id', $request->chapter_id)->get();
        if (!empty($request->content_id)) {
            foreach ($request->content_id as $key => $id) {
                $content = $contents->find($id);
                if ($content) {
                    $content->position = ++$key;
                    $content->save();
                }
            }
        }
    }

    private function updateTotalCountForCourse($course): void
    {
        $course->total_chapters = count($course->chapters);
        $course->total_lessons = count($course->lessons);
        $course->total_quiz_lessons = count($course->lessonQuizzes);
        $course->total_comments = count($course->comments);
        $course->total_reviews = count($course->reviews);
        $course->save();
    }
}
