<?php

namespace App\Repositories\Eloquents;

use App\Http\Resources\api\v2\Badge\ActiveBadgeListResource;
use App\Http\Resources\api\v2\Student\StudentListResource;
use App\Http\Resources\api\v2\User\UserDetailsResource;
use App\Repositories\Interfaces\AdminRepositoryInterface;
use App\TopicReport;
use App\User;
use Modules\CourseSetting\Entities\Course;
use Modules\CourseSetting\Entities\CourseEnrolled;
use Modules\Setting\Entities\Badge;

class AdminRepository implements AdminRepositoryInterface
{
    public function studentList(object $request): object
    {
        $students = User::query();
        if (isModuleActive('LmsSaas')) {
            $students->where('lms_id', app('institute')->id)
                ->whereHas('role', function ($role) {
                    $role->where('id', 3);
                })
                ->when($request->search, function ($query) use ($request) {
                    $query->where('name', 'like', "%$request->search%")
                        ->orWhere('email', 'like', "%$request->search%")
                        ->orWhere('phone', 'like', "%$request->search%");
                });
        } else {
            $students->where('lms_id', 1)
                ->whereHas('role', function ($role) {
                    $role->where('id', 3);
                })
                ->when($request->search, function ($query) use ($request) {
                    $query->where('name', 'like', "%$request->search%")
                        ->orWhere('email', 'like', "%$request->search%")
                        ->orWhere('phone', 'like', "%$request->search%");
                });
        }
        if (isModuleActive('UserType')) {
            $students->whereHas('userRoles', function ($q) {
                $q->where('role_id', 3);
            })
                ->when($request->search, function ($query) use ($request) {
                    $query->where('name', 'like', "%$request->search%")
                        ->orWhere('email', 'like', "%$request->search%")
                        ->orWhere('phone', 'like', "%$request->search%");
                });
        } else {
            $students->whereHas('role', function ($role) {
                $role->where('id', 3);
            })
                ->when($request->search, function ($query) use ($request) {
                    $query->where('name', 'like', "%$request->search%")
                        ->orWhere('email', 'like', "%$request->search%")
                        ->orWhere('phone', 'like', "%$request->search%");
                });
        }
        return StudentListResource::collection($students->paginate($request->per_page ?? 15));
    }

    public function changeStudentStatus(object $request): void
    {
        $student = User::whereHas('role', function ($role) {
            $role->where('id', 3);
        })->where('is_active', 1)->find($request->student_id);
        $student->update([
            'status' => (bool)$request->status
        ]);
    }

    public function studentDetail(object $request): object
    {
        $student = User::whereHas('role', function ($role) {
            $role->where('id', 3);
        })
            ->with('userInfo')
            ->where('is_active', 1)->find($request->student_id);
        return new UserDetailsResource($student);
    }

    public function dashboard(): array
    {
        $courses = Course::with('user', 'enrolls');

        if (isModuleActive('Organization') && auth()->user()->isOrganization()) {
            $courses->whereHas('user', function ($q) {
                $q->where('organization_id', auth()->id());
                $q->orWhere('id', auth()->id());
            });
        }
        $course = $courses->get();

        $data['academic_status'] = [
            'course'    => empty($course->where('type', 1)->where('status', 1)),
            'quize'     => empty($course->where('type', 2)->where('status', 1)),
            'class'     => empty($course->where('type', 3)->where('status', 1)),
        ];

        $data['info'] = [
            'course'                => (int)$course->where('type', 1)->where('status', 1)->count(),
            'enrolled'              => (int)CourseEnrolled::where('status', 1)->count(),
            'enrolled_amount'       => (float)CourseEnrolled::where('status', 1)->sum('purchase_price'),
            'enrolled_today'        => (float)CourseEnrolled::where('status', 1)->where('created_at', date('Y-m-d'))->sum('purchase_price'),
            'this_month_enrolled'   => (float)CourseEnrolled::where('status', 1)->whereYear('created_at', date('Y'))->whereMonth('created_at', date('m'))->sum('purchase_price'),
            'revenue'               => (float)CourseEnrolled::where('status', 1)->sum('reveune'),
        ];

        $data['overview_topics'] = [
            'active_topics'     => (int)$course->where('status', 1)->count(),
            'pending_topics'    => (int)$course->where('status', 0)->count(),
            'courses'           => (int)$course->where('type', 1)->count(),
            'quizes'            => (int)$course->where('type', 2)->count(),
            'classes'           => (int)$course->where('type', 3)->count(),
            'others'            => (int)TopicReport::count(),
        ];

        $data['upcoming_badges'] = ActiveBadgeListResource::collection(Badge::where('status', 1)->get());

        return $data;
    }
}
