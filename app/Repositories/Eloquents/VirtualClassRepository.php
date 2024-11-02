<?php

namespace App\Repositories\Eloquents;

use App\Http\Resources\api\v2\Certificate\CertificateListResource;
use App\Http\Resources\api\v2\VirtualClass\ClassDetailResource;
use App\Http\Resources\api\v2\VirtualClass\ClassListResource;
use App\Http\Resources\api\v2\VirtualClass\ClassScheduleListResource;
use App\Notifications\GeneralNotification;
use App\Repositories\Interfaces\VirtualClassRepositoryInterface;
use App\Traits\UploadMedia;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Modules\BBB\Entities\BbbMeeting;
use Modules\BBB\Entities\BbbMeetingUser;
use Modules\BBB\Entities\BbbSetting;
use Modules\BBB\Http\Controllers\BbbMeetingController;
use Modules\Certificate\Entities\Certificate;
use Modules\CourseSetting\Entities\Course;
use Modules\Forum\Services\ForumService;
use Modules\GoogleCalendar\Entities\GoogleCalendarEvent;
use Modules\GoogleCalendar\Events\GoogleCalendarDeleteEvent;
use Modules\GoogleCalendar\Events\GoogleCalendarSyncEvent;
use Modules\GoogleMeet\Entities\GoogleMeetMeeting;
use Modules\GoogleMeet\Events\MeetingDeleteEvent;
use Modules\GoogleMeet\Events\MeetingSyncEvent;
use Modules\InAppLiveClass\Entities\InAppLiveClassMeetingUser;
use Modules\InAppLiveClass\Http\Controllers\InAppLiveClassController;
use Modules\Jitsi\Entities\JitsiMeeting;
use Modules\Jitsi\Entities\JitsiMeetingUser;
use Modules\Jitsi\Http\Controllers\JitsiMeetingController;
use Modules\Membership\Repositories\Interfaces\MembershipVirtualClassRepositoryInterface;
use Modules\VirtualClass\Entities\ClassComplete;
use Modules\VirtualClass\Entities\VirtualClass;
use Modules\VirtualClass\Http\Controllers\CustomMeetingController;
use Modules\Zoom\Entities\ZoomMeeting;
use Modules\Zoom\Entities\ZoomMeetingUser;
use Modules\Zoom\Entities\ZoomSetting;
use Modules\Zoom\Http\Controllers\MeetingController;

class VirtualClassRepository implements VirtualClassRepositoryInterface
{
    use UploadMedia;
    public function store(object $request): bool
    {
        $class = new VirtualClass();
        if (isModuleActive('Membership')) {
            if ($request->filled('is_membership')) {
                $class->is_membership = 1;
            }
        }
        foreach ($request->title as $key => $title) {
            $class->setTranslation('title', $key, $title);
        }
        if (showEcommerce()) {
            if ($request->free) {
                $class->fees = 0;
            } else {
                $class->fees = (float)$request->fees;
            }
        } else {
            $class->fees = 0;
        }
        $class->duration        = $request->duration;
        $class->category_id     = (int)$request->category;
        $class->sub_category_id = (int)$request->sub_category;
        $class->type            = (int)$request->get('type', 0);
        $class->host            = $request->host;
        $class->lang_id         = $request->lang_id;
        $class->capacity        = $request->capacity;

        if ($request->type == 1) {
            if ($request->start_date) {
                $class->start_date = Carbon::createFromFormat(getActivePhpDateFormat(), $request->start_date)->format('Y-m-d');
            }
            if ($request->end_date) {
                $class->end_date = Carbon::createFromFormat(getActivePhpDateFormat(), $request->end_date)->format('Y-m-d');
            }
        } else {
            if ($request->date) {
                $class->start_date  = Carbon::createFromFormat(getActivePhpDateFormat(), $request->date)->format('Y-m-d');
                $class->end_date    = Carbon::createFromFormat(getActivePhpDateFormat(), $request->date)->format('Y-m-d');
            }
        }
        if ($request->time) {
            $class->time = date("H:i", strtotime($request->time));
        }

        $class->save();
        $course = new Course();
        $course->scope      = $request->scope;
        $course->class_id   = $class->id;
        $course->user_id    = auth()->id();
        $course->lang_id    = $request->lang_id;
        if (showEcommerce()) {
            if ($request->free) {
                $course->price = 0;
            } else {
                $course->price = (float)$request->fees;
            }
        } else {
            $course->price = 0;
        }

        //for support ticket
        if (isModuleActive('SupportTicket') && Schema::hasColumn('courses', 'support')) {
            if (isset($request->support)) {
                $course->support = true;
            } else {
                $course->support = false;
            }
        }

        foreach ($request->title as $key => $title) {
            $course->setTranslation('title', $key, $title);
        }

        foreach ($request->description as $key => $about) {
            $course->setTranslation('about', $key, $about);
        }

        $course->required_type = (int)$request->required_type;

        if (Settings('frontend_active_theme') == "edume") {
            $course->what_learn1 = $request->what_learn1;
            $course->what_learn2 = $request->what_learn2;
        }

        if (isModuleActive('CertificatePro') && Settings('use_certificate_template') == 'pro') {
            $course->pro_certificate_id = (int)$request->get('certificate', 0);
        } else {
            $course->certificate_id = (int)$request->get('certificate', 0);
        }

        if ($request->assign_instructor) {
            $course->user_id = $request->assign_instructor;
        }
        if ($request->assistant_instructors) {
            $assistants = $request->assistant_instructors;
            if (($key = array_search($course->user_id, $assistants)) !== false) {
                unset($assistants[$key]);
            }
            if ($assistants) {
                $course->assistant_instructors = json_encode(array_values($assistants));
            }
        }
        $course->type = 3;
        $course->save();

        if ($request->image) {
            $image = $this->generateLink($request->image, $course->id, get_class($course), 'image');
            $course->image = $image;
            $course->thumbnail = $image;
        }
        $course->save();

        $start_date = strtotime($class['start_date']);
        $end_date = strtotime($class['end_date']);
        if ($class->type == 0) {
            $end_date = strtotime($class['start_date']);
        }

        $datediff = $end_date - $start_date;

        $days = ceil($datediff / (60 * 60 * 24)) + 1;

        $class->duration = $request->duration;

        $class->total_class = $days;

        $class->save();
        if (isModuleActive('Forum')) {
            $forumService = new ForumService();
            $forumService->autoTopic('topic', $course);
        }

        if ($days != 0) {
            for (
                $i = 0;
                $i < $days;
                $i++
            ) {
                $new_date = date('m/d/Y', strtotime($class['start_date'] . '+' . $i . ' day'));

                if (isModuleActive('GoogleCalendar') && $class->host != 'GoogleMeet' && isset($request->allow_google_calendar) && $request->allow_google_calendar) {
                    $custom_date = [
                        'main_model'    => VirtualClass::class,
                        'main_model_id' => $class->id,
                        'sub_model'     => null,
                        'sub_model_id'  => null,
                    ];
                    Event::dispatch(new GoogleCalendarSyncEvent([], $custom_date, $class->id, $new_date));
                }

                if ($class->host == "Zoom") {
                    $fileName = "";
                    if ($request->file('attached_file') != "") {
                        $file = $request->file('attached_file');
                        $ignore = strtolower($file->getClientOriginalExtension());
                        if ($ignore != 'php') {
                            $fileName = $request->topic . time() . "." . $file->getClientOriginalExtension();
                            $file->move('public/uploads/zoom-meeting/', $fileName);
                            $fileName = 'public/uploads/zoom-meeting/' . $fileName;
                        }
                    }
                    $result = $this->createClassWithZoom($class, $new_date, $request, $fileName);
                } elseif ($class->host == "BBB") {
                    if (isModuleActive('BBB')) {
                        $result = $this->createClassWithBBB($class, $new_date, $request);
                    }
                } elseif ($class->host == "Jitsi") {
                    if (isModuleActive('Jitsi')) {
                        $result = $this->createClassWithJitsi($class, $new_date, $request);
                    }
                } elseif ($class->host == "InAppLiveClass") {
                    if (isModuleActive('InAppLiveClass')) {
                        $agoraSettings = [
                            'chat' => (bool)$request->in_app_chat,
                            'audio' => (bool)$request->in_app_audio,
                            'video' => (bool)$request->in_app_video,
                            'share_screen' => (bool)$request->in_app_share_screen,
                        ];
                        $class->host_setting = json_encode($agoraSettings);
                        $class->save();
                        $this->createClassWithInAppLiveClass($class, $new_date, $request);
                    }
                } elseif ($class->host == "GoogleMeet") {
                    if (isModuleActive('GoogleMeet')) {
                        $response = Event::dispatch(new MeetingSyncEvent($class->id, $new_date));
                        $response[0];
                    }
                } elseif ($class->host == "Custom") {
                    $this->createClassWithCustom($class, $new_date, $request);
                }

                if (isModuleActive('Membership')) {
                    $request->merge([
                        'virtual_class_id' => $class->id,
                    ]);

                    $membershipInterface = App::make(MembershipVirtualClassRepositoryInterface::class);
                    $membershipInterface->storeVirtualClassMember($request->except(['_token', 'url']));
                }
            }
        }

        return true;
    }

    public function instructors(object $request): object
    {
        return User::whereIn('role_id', [1, 2])
            ->when($search = $request->search, function ($user) use ($search) {
                $user->whereLike('name', $search);
            })->where('status', 1)
            ->select('id', 'name')
            ->paginate($request->per_page ?? 10)->makeHidden('blocked_by_me');
    }

    public function certificateTypes(): object
    {
        return CertificateListResource::collection(Certificate::get());
    }

    public function classList(object $request): object
    {
        $data = VirtualClass::where('status', 1)->when($request->search, function ($class) use ($request) {
            $class->whereLike('title', $request->search);
        })->paginate($request->per_page ?? 10);

        return ClassListResource::collection($data);
    }

    public function classDetail(object $request): object
    {
        $rules = ['class_id' => 'required|exists:virtual_classes,id'];
        $request->validate($rules, validationMessage($rules));

        $data = VirtualClass::where('status', 1)->find($request->class_id);

        return new ClassDetailResource($data);
    }

    public function classSchedules(object $request): object
    {
        $class = VirtualClass::find($request->class_id);
        return new ClassScheduleListResource($class);
    }
    public function updateClass(object $request): bool
    {
        $class = VirtualClass::find($request->class_id);
        foreach ($request->title as $key => $title) {
            $class->setTranslation('title', $key, $title);
        }
        $class->duration = $request->duration;
        $class->category_id = $request->category;
        $class->sub_category_id = $request->sub_category;
        if (showEcommerce()) {
            if ($request->free == '0') {
                $class->fees = 0;
            } else {
                $class->fees = (float)$request->fees;
            }
        } else {
            $class->fees = 0;
        }

        $class->type = (int)$request->get('type', 0);


        if ($request->type == 0) {
            if (!empty($request->date)) {
                $class->start_date = Carbon::createFromFormat('m-d-Y', $request->date)->format('Y-m-d');
                $class->end_date = Carbon::createFromFormat('m-d-Y', $request->date)->format('Y-m-d');
            }
        } else {
            if (!empty($request->start_date)) {
                $class->start_date = Carbon::createFromFormat('m-d-Y', $request->start_date)->format('Y-m-d');
            }
            if (!empty($request->end_date)) {
                $class->end_date = Carbon::createFromFormat('m-d-Y', $request->end_date)->format('Y-m-d');
            }
        }

        if (!empty($request->time)) {
            $class->time = date("H:i", strtotime($request->time));
        }


        $class->capacity = $request->capacity;

        $class->save();

        $course = Course::where('class_id', $request->class_id)->where('type', 3)->first();
        $course->scope = $request->scope;
        if (!empty($request->assign_instructor)) {
            $course->user_id = $request->assign_instructor;
        }


        if (!empty($request->assistant_instructors)) {
            $assistants = $request->assistant_instructors;
            if (($key = array_search($course->user_id, $assistants)) !== false) {
                unset($assistants[$key]);
            }
            if (!empty($assistants)) {
                $course->assistant_instructors = json_encode(array_values($assistants));
            }
        }
        if (isModuleActive('Org')) {
            $course->required_type = $request->required_type;
        } else {
            $course->required_type = 0;
        }
        //for support ticket
        if (isModuleActive('SupportTicket') && Schema::hasColumn('courses', 'support')) {
            if (isset($request->support)) {
                $course->support = true;
            } else {
                $course->support = false;
            }
        }

        $course->lang_id = 1;
        foreach ($request->title as $key => $title) {
            $course->setTranslation('title', $key, $title);
        }

        foreach ($request->description as $key => $about) {
            $course->setTranslation('about', $key, $about);
        }
        if (showEcommerce()) {
            if ($request->free == '0') {
                $course->price = 0;
            } else {
                $course->price = (float)$request->fees;
            }
        } else {
            $course->price = 0;
        }
        if (Settings('frontend_active_theme') == "edume") {
            $course->what_learn1 = $request->what_learn1;
            $course->what_learn2 = $request->what_learn2;
        }

        if (isModuleActive('CertificatePro') && Settings('use_certificate_template') == 'pro') {
            $course->pro_certificate_id = (int)$request->get('certificate', 0);
        } else {
            $course->certificate_id = (int)$request->get('certificate', 0);
        }


        $class->category_id = (int)$request->category;
        $class->sub_category_id = (int)$request->sub_category;

        $course->image = null;
        $course->thumbnail = null;
        $course->save();


        $this->removeLink($course->id, get_class($course));
        if ($request->image) {
            $userImage = $this->generateLink($request->image, $course->id, get_class($course), 'image');
            $course->image = $userImage;
            $course->thumbnail = $userImage;
        }
        $course->save();

        $start_date = strtotime($class['start_date']);
        $end_date = strtotime($class['end_date']);
        if ($class->type == 0) {
            $end_date = strtotime($class['start_date']);
        }

        $datediff = $end_date - $start_date;
        $totalClass = ceil($datediff / (60 * 60 * 24)) + 1;

        if (isModuleActive('GoogleCalendar') && $class->host != 'GoogleMeet') {
            $custom_date = [
                'main_model' => VirtualClass::class,
                'main_model_id' => $class->id,
                'sub_model' => null,
                'sub_model_id' => null,
            ];

            $all_events = GoogleCalendarEvent::where('main_model', VirtualClass::class)->where('main_model_id', $class->id)->get();

            foreach ($all_events as $event) {
                if ($event->google_calendar_id) {
                    Event::dispatch(new GoogleCalendarDeleteEvent($event->google_calendar_id));
                }
                $event->delete();
            }

            if ($totalClass != 0 && isset($request->allow_google_calendar) && $request->allow_google_calendar) {
                for (
                    $i = 0;
                    $i < $totalClass;
                    $i++
                ) {
                    $new_date = date('m/d/Y', strtotime($class['start_date'] . '+' . $i . ' day'));

                    Event::dispatch(new GoogleCalendarSyncEvent([], $custom_date, $class->id, $new_date));
                }
            }
        }

        if ($class->host == "Zoom") {
            $all = $class->zoomMeetings;
            foreach ($all as $zoom) {

                if (file_exists($zoom->attached_file)) {
                    unlink($zoom->attached_file);
                }
                ZoomMeetingUser::where('meeting_id', $zoom->meeting_id)->delete();
                $zoom->delete();
                $class->total_class = $class->total_class - 1;
                $class->save();
            }

            if ($totalClass != 0) {
                for (
                    $i = 0;
                    $i < $totalClass;
                    $i++
                ) {
                    $new_date = date('m/d/Y', strtotime($class['start_date'] . '+' . $i . ' day'));

                    $this->createClassWithZoom($class, $new_date, $request, null);
                }
            }
        } elseif ($class->host == "BBB") {
            $all = $class->bbbMeetings;
            foreach ($all as $bbb) {
                BbbMeetingUser::where('meeting_id', $bbb->id)->delete();
                $bbb->delete();
                $class->total_class = $class->total_class - 1;
                $class->save();
            }

            if ($totalClass != 0) {
                for (
                    $i = 0;
                    $i < $totalClass;
                    $i++
                ) {
                    $new_date = date('m/d/Y', strtotime($class['start_date'] . '+' . $i . ' day'));


                    if (isModuleActive('BBB')) {
                        $this->createClassWithBBB($class, $new_date, $request);
                    } else {
                        return response()->json(['success' => false, 'message' => 'Module not installed yet']);
                    }
                }
            }
        } elseif ($class->host == "Jitsi") {
            $all = $class->jitsiMeetings;


            foreach ($all as $jitsi) {
                JitsiMeetingUser::where('meeting_id', $jitsi->id)->delete();
                $jitsi->delete();
                $class->total_class = $class->total_class - 1;
                $class->save();
            }

            if ($totalClass != 0) {
                for (
                    $i = 0;
                    $i < $totalClass;
                    $i++
                ) {
                    $new_date = date('m/d/Y', strtotime($class['start_date'] . '+' . $i . ' day'));

                    if (isModuleActive('Jitsi')) {
                        $this->createClassWithJitsi($class, $new_date, $request);
                    } else {
                        return response()->json(['success' => false, 'message' => 'Module not installed yet']);
                    }
                }
            }
        } elseif ($class->host == "InAppLiveClass") {
            $all = $class->inAppMeetings;

            foreach ($all as $inApp) {
                InAppLiveClassMeetingUser::where('meeting_id', $inApp->id)->delete();
                $inApp->delete();
                $class->total_class = $class->total_class - 1;
                $class->save();
            }

            if ($totalClass != 0) {
                for (
                    $i = 0;
                    $i < $totalClass;
                    $i++
                ) {
                    $new_date = date('m/d/Y', strtotime($class['start_date'] . '+' . $i . ' day'));

                    if (isModuleActive('InAppLiveClass')) {
                        $this->createClassWithInAppLiveClass($class, $new_date, $request);
                    } else {
                        return response()->json(['success' => false, 'message' => 'Module not installed yet']);
                    }
                }
            }
        } elseif ($class->host == "GoogleMeet") {
            $all = $class->googleMeetMeetings;
            foreach ($all as $meet) {
                if ($meet->meetingId) {
                    Event::dispatch(new MeetingDeleteEvent($meet->meetingId));
                }

                $meet->delete();
                $class->total_class = $class->total_class - 1;
                $class->save();
            }

            if ($totalClass != 0) {
                for (
                    $i = 0;
                    $i < $totalClass;
                    $i++
                ) {
                    $new_date = date('m/d/Y', strtotime($class['start_date'] . '+' . $i . ' day'));
                    Event::dispatch(new MeetingSyncEvent($class->id, $new_date));
                }
            }
        }

        $this->deleteClassComplete($course->id, $class->id);

        $datediff = $end_date - $start_date;
        $totalClass = ceil($datediff / (60 * 60 * 24)) + 1;
        $class->total_class = $totalClass;
        $data = $class->save();

        $receivers = $class->course->enrollUsers;
        if ($class->type == 0) {
            $message = "Your virtual class " . $class->getTranslation('title', app()->getLocale()) . " has been updated. Date :" . showDate($class->start_date) . "and Time is :" . $class->time;
        } else {
            $message = "Your virtual class " . $class->getTranslation('title', app()->getLocale()) . " has been updated. Date :" . showDate($class->start_date) .
                "To " . showDate($class->end_date) . "and Time is :" . $class->time;
        }

        foreach ($receivers as $key => $receiver) {
            $details = [
                'title' => 'Virtual Class Update ',
                'body' => $message,
                'actionText' => 'Visit',
                'actionURL' => route('classDetails', $class->course->slug),
            ];
            try {
                Notification::send($receiver, new GeneralNotification($details));
            } catch (\Exception $exception) {
                //$exception;
            }
        }

        return true;
    }

    public function changeStatus(object $request): bool
    {
        VirtualClass::find($request->class_id)->course->update([
            'status' => (bool)$request->status,
        ]);
        return true;
    }

    public function deleteClass(object $request): bool
    {
        $virtualClass = VirtualClass::find($request->class_id);

        $carts = $virtualClass->course->carts;

        foreach ($carts as $cart) {
            $cart->delete();
        }

        if ($virtualClass) {
            if ($virtualClass->host == "BBB") {
                if (isModuleActive('BBB')) {
                    $bbbClass = BbbMeeting::where('class_id', $virtualClass->id)->get();
                    $bbbClass->each->delete();
                }
            } elseif ($virtualClass->host == 'Zoom') {
                $zoomClass = ZoomMeeting::where('class_id', $virtualClass->id)->get();

                foreach ($zoomClass as $cls) {
                    $cls->delete();
                }
            } elseif ($virtualClass->host == 'Jitsi') {
                if (isModuleActive('Jitsi')) {
                    $JitsiClass = JitsiMeeting::where('class_id', $virtualClass->id)->get();
                    $JitsiClass->each->delete();
                }
            } elseif ($virtualClass->host == 'GoogleMeet') {
                if (isModuleActive('GoogleMeet')) {
                    $all = GoogleMeetMeeting::where('class_id', $virtualClass->id)->get();
                    foreach ($all as $meet) {
                        if ($meet->meetingId) {
                            Event::dispatch(new MeetingDeleteEvent($meet->meetingId));
                        }
                        $meet->delete();
                    }
                }
            }
        }
        $course = $virtualClass->course;

        if ($course && $virtualClass) {
            $this->deleteClassComplete($course->id, $virtualClass->id);
        }

        if ($course) {
            $course->delete();
        }

        if ($virtualClass) {
            $virtualClass->delete();
        }

        return true;
    }

    public function deleteSchedule(object $request): bool
    {
        $class = VirtualClass::findOrFail($request->class_id);
        switch ($class->host) {
            case 'Zoom':
                $schedule = $class->zoomMeetings->where('class_id', $request->class_id)->find($request->schedule_id);
                break;
            case 'BBB':
                $schedule = $class->zoomMeetings->find($request->schedule_id);
                break;
            case 'Jitsi':
                $schedule = $class->jitsiMeetings->find($request->schedule_id);
                break;
            case 'InAppLiveClass':
                $schedule = $class->inAppMeetings->find($request->schedule_id);
                break;
            default:
                $schedule = $class->customMeetings->find($request->schedule_id);
                break;
        }

        $schedule->delete();

        return true;
    }
    public function addPricePlan(object $request): bool
    {
        $rules = ['class_id' => 'required|exists:virtual_classes,id'];
        $request->validate($rules, validationMessage($rules));

        $request->replace([
            'course_id' => VirtualClass::find($request->class_id)->course->id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'title' => $request->title,
            'discount' => $request->discount,
            'capacity' => $request->capacity,
        ]);
        (new CoursePricePlanRepository)->storePlan($request);
        return true;
    }

    public function deletePricePlan(object $request): bool
    {
        $request->replace([
            'course_id' => VirtualClass::find($request->class_id)->course->id,
            'price_plan_id' => $request->price_plan_id,
        ]);
        (new CoursePricePlanRepository)->deletePlan($request);
        return true;
    }

    public function updatePricePlan(object $request): bool
    {
        $request->replace([
            'course_id' => VirtualClass::find($request->class_id)->course->id,
            'price_plan_id' => $request->price_plan_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'title' => $request->title,
            'discount' => $request->discount,
            'capacity' => $request->capacity,
        ]);
        (new CoursePricePlanRepository)->updatePlan($request);
        return true;
    }

    private function createClassWithZoom($class, $date, $request, $fileName)
    {
        $meeting = new MeetingController();
        $data = [];
        $data['instructor_id']          = auth()->user()->id;
        $data['class_id']               = $class->id;
        $data['topic']                  = $class->getTranslation('title', app()->getLocale());
        $data['date']                   = $date;
        $data['description']            = $class->course->getTranslation('about', app()->getLocale());
        $data['password']               = $request->password;
        $data['attached_file']          = $fileName;
        $data['time']                   = $request->time;
        $data['duration']               = $request->duration;
        $data['is_recurring']           = $request->is_recurring;
        $data['recurring_type']         = $request->recurring_type;
        $data['recurring_repect_day']   = $request->recurring_repect_day;
        $data['recurring_end_date']     = $request->recurring_end_date;

        $setting = ZoomSetting::getData();

        $data['approval_type']      = $setting->approval_type;
        $data['auto_recording']     = $setting->auto_recording;
        $data['waiting_room']       = $setting->waiting_room;
        $data['audio']              = $setting->audio;
        $data['mute_upon_entry']    = $setting->mute_upon_entry;
        $data['host_video']         = $setting->host_video;
        $data['participant_video']  = $setting->participant_video;
        $data['join_before_host']   = $setting->join_before_host;

        $token = $meeting->createZoomToken();

        return $meeting->classStore($token, $data);
    }

    private function createClassWithBBB($class, $date, $request)
    {
        $data = [];
        $setting                                            = BbbSetting::getData();
        $data['topic']                                      = $class->getTranslation('title', app()->getLocale());
        $data['instructor_id']                              = auth()->user()->id;
        $data['class_id']                                   = $class->id;
        $data['attendee_password']                          = $request->attendee_password;
        $data['moderator_password']                         = $request->moderator_password;
        $data['date']                                       = $date;
        $data['time']                                       = $class->time;
        $data['welcome_message']                            = $setting->welcome_message;
        $data['dial_number']                                = $setting->dial_number;
        $data['max_participants']                           = $setting->max_participants;
        $data['logout_url']                                 = $setting->logout_url;
        $data['record']                                     = $setting->record;
        $data['duration']                                   = $request->duration;
        $data['is_breakout']                                = $setting->is_breakout;
        $data['moderator_only_message']                     = $setting->moderator_only_message;
        $data['auto_start_recording']                       = $setting->auto_start_recording;
        $data['allow_start_stop_recording']                 = $setting->allow_start_stop_recording;
        $data['webcams_only_for_moderator']                 = $setting->webcams_only_for_moderator;
        $data['copyright']                                  = $setting->copyright;
        $data['mute_on_start']                              = $setting->mute_on_start;
        $data['lock_settings_disable_mic']                  = $setting->lock_settings_disable_mic;
        $data['lock_settings_disable_private_chat']         = $setting->lock_settings_disable_private_chat;
        $data['lock_settings_disable_public_chat']          = $setting->lock_settings_disable_public_chat;
        $data['lock_settings_disable_note']                 = $setting->lock_settings_disable_note;
        $data['lock_settings_locked_layout']                = $setting->lock_settings_locked_layout;
        $data['lock_settings_lock_on_join']                 = $setting->lock_settings_lock_on_join;
        $data['lock_settings_lock_on_join_configurable']    = $setting->lock_settings_lock_on_join_configurable;
        $data['guest_policy']                               = $setting->guest_policy;
        $data['redirect']                                   = $setting->redirect;
        $data['join_via_html5']                             = $setting->join_via_html5;
        $data['state']                                      = $setting->state;
        $datetime                                           = $date . " " . $class->time;
        $data['datetime']                                   = strtotime($datetime);

        $meeting    = new BbbMeetingController();
        $result     = $meeting->classStore($data);
        return $result;
    }

    private function createClassWithJitsi($class, $date, $request)
    {
        $data                       = [];
        $data['topic']              = $class->getTranslation('title', app()->getLocale());
        $data['description']        = $class->course->getTranslation('about', app()->getLocale());
        $data['duration']           = $request->duration;
        $data['jitsi_meeting_id']   = $request->jitsi_meeting_id;
        $data['instructor_id']      = auth()->user()->id;
        $data['class_id']           = $class->id;
        $data['date']               = $date;
        $data['time']               = $request->time;

        $meeting    = new JitsiMeetingController();
        $result     = $meeting->classStore($data);
        return $result;
    }

    private function createClassWithInAppLiveClass($class, $date, $request)
    {
        $data                       = [];
        $data['topic']              = $class->getTranslation('title', app()->getLocale());
        $data['description']        = $class->course->getTranslation('about', app()->getLocale());
        $data['duration']           = $request->duration;
        $data['jitsi_meeting_id']   = $request->jitsi_meeting_id;
        $data['instructor_id']      = auth()->user()->id;
        $data['class_id']           = $class->id;
        $data['date']               = $date;
        $data['time']               = $request->time;

        $meeting    = new InAppLiveClassController();
        $result     = $meeting->classStore($data);
        return $result;
    }

    private function createClassWithCustom($class, $date, $request)
    {
        $data['topic']          = $class->getTranslation('title', app()->getLocale());
        $data['description']    = $class->course->getTranslation('about', app()->getLocale());
        $data['duration']       = $request->duration;
        $data['instructor_id']  = auth()->user()->id;
        $data['class_id']       = $class->id;
        $data['date']           = $date;
        $data['time']           = $request->time;

        $meeting = new CustomMeetingController();
        return $meeting->classStore($data);
    }

    private function deleteClassComplete($course_id, $class_id)
    {
        $completes = ClassComplete::where('course_id', $course_id)->where('class_id', $class_id)->get();
        foreach ($completes as $complete) {
            $complete->delete();
        }
        return true;
    }
}
