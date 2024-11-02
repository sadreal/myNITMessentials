<?php

namespace App\Repositories\Eloquents;

use App\Http\Resources\api\v2\Lesson\LessonListResource;
use App\Jobs\SendGeneralEmail;
use App\Repositories\Interfaces\LessonRepositoryInterface;
use App\Traits\BunnySettings;
use App\Traits\Filepond;
use App\Traits\Gdrive;
use App\Traits\UploadMedia;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Modules\Assignment\Entities\InfixAssignment;
use Modules\BunnyStorage\Entities\BunnyLesson;
use Modules\BunnyStorage\Http\Controllers\BunnyStreamController;
use Modules\CourseSetting\Entities\Chapter;
use Modules\CourseSetting\Entities\Course;
use Modules\CourseSetting\Entities\Lesson;
use Modules\CourseSetting\Entities\LessonFile;
use Modules\CourseSetting\Http\Controllers\CourseSettingController;
use Modules\CourseSetting\Http\Controllers\VimeoController;
use Modules\H5P\Http\Controllers\H5PController;
use Modules\SCORM\Http\Controllers\SCORMController;
use Modules\VdoCipher\Http\Controllers\VdoCipherController;
use Modules\XAPI\Http\Controllers\XAPIController;

class LessonRepository implements LessonRepositoryInterface
{
    use Filepond, UploadMedia, BunnySettings, Gdrive;

    /* public function addLesson(object $request): object
    {
        $request->validate([
            'name'          => 'required',
            'chapter_id'    => 'required',
            'duration'      => 'required',
            'course_id'     => 'required',
            'video_url'     => 'required'
        ]);

        $privacy = $request->privacy == 'locked' || $request->privacy ==  ucwords('locked') || $request->privacy ==  strtoupper('locked') ? 1 : 0;

        $user = auth()->user();
        if ($user->role_id == 2) {
            $course = Course::where('id', $request->course_id)->where('user_id', auth()->id())->first();
        } else {
            $course = Course::where('id', $request->course_id)->first();
        }

        $chapter = Chapter::find($request->chapter_id);

        if (!$course && !$chapter) {
            $response = [
                'success'   => false,
                'message'   => trans('api.Operation failed')
            ];
        } else {
            $lesson = Lesson::create([
                'course_id'     => $request->course_id,
                'chapter_id'    => $request->chapter_id,
                'name'          => $request->name,
                'description'   => $request->description,
                'video_url'     => $request->video_url,
                'host'          => $request->host,
                'duration'      => $request->duration,
                'is_lock'       => $privacy
            ]);

            try {
                if (isset($course->enrollUsers) && !empty($course->enrollUsers)) {
                    foreach ($course->enrollUsers as $user) {
                        if (UserEmailNotificationSetup('Course_Lesson_Added', $user)) {
                            SendGeneralEmail::dispatch($user, 'Course_Lesson_Added', [
                                'time'      => Carbon::now()->format('d-M-Y, g:i A'),
                                'course'    => $course->title,
                                'chapter'   => $chapter->name,
                                'lesson'    => $lesson->name,
                            ]);
                        }
                        if (UserBrowserNotificationSetup('Course_Lesson_Added', $user)) {

                            send_browser_notification(
                                $user,
                                'Course_Lesson_Added',
                                [
                                    'time'      => Carbon::now()->format('d-M-Y, g:i A'),
                                    'course'    => $course->title,
                                    'chapter'   => $chapter->name,
                                    'lesson'    => $lesson->name,
                                ],
                                trans('common.View'),
                                courseDetailsUrl($course->id, $course->type, $course->slug),
                            );
                        }

                        if (UserMobileNotificationSetup('Course_Lesson_Added', $user) && !empty($user->device_token)) {
                            send_mobile_notification($user, 'Course_Lesson_Added', [
                                'time'      => Carbon::now()->format('d-M-Y, g:i A'),
                                'course'    => $course->title,
                                'chapter'   => $chapter->name,
                                'lesson'    => $lesson->name,
                            ]);
                        }

                        if (UserSmsNotificationSetup('Course_Lesson_Added', $user)) {

                            send_sms_notification($user, 'Course_Lesson_Added', [
                                'time'      => Carbon::now()->format('d-M-Y, g:i A'),
                                'course'    => $course->title,
                                'chapter'   => $chapter->name,
                                'lesson'    => $lesson->name,
                            ]);
                        }
                    }
                }
            } catch (\Exception $e) {
                // return response()->json(['message' => $e->getMessage()]);
            }
            $data = [
                'course_id'         => $lesson->course_id,
                'chapter_id'        => $lesson->chapter_id,
                'chapter_name'      => $lesson->name,
                'description'       => $lesson->description,
                'video_url'         => $lesson->video_url,
                'chapter_host'      => $lesson->host,
                'chapter_duration'  => $lesson->duration,
                'is_lock'           => (bool) $lesson->is_lock
            ];

            $response = [
                'success'   => true,
                'data'      => $data,
                'message'   => trans('api.Operation successful')
            ];
        }
        return response()->json($response);
    } */

    public function addLesson(object $request): object
    {
        $user = auth()->user();

        if ($user->role_id == 2) {
            $course = Course::where('user_id', auth()->id())->find($request->course_id);
        } else {
            $course = Course::find($request->course_id);
        }

        $chapter = Chapter::where('course_id', $course->id)->find($request->chapter_id);

        if (isset($course) && isset($chapter)) {
            $lesson = new Lesson();
            $lesson->course_id = $request->course_id;
            $lesson->chapter_id = $request->chapter_id;
            $lesson->name = $request->name;
            $lesson->description = $request->description;

            if ($request->get('host') == "Vimeo") {
                if (config('vimeo.connections.main.upload_type') == "Direct") {
                    $vimeoController = new VimeoController();
                    $lesson->video_url = $vimeoController->uploadFileIntoVimeo(md5(time()), $request->vimeo);
                } else {
                    $lesson->video_url = $request->vimeo;
                }
            } elseif ($request->get('host') == "BunnyStorage" && isModuleActive('BunnyStorage')) {
                $settings = $this->getSettings();

                $bunny_lesson_data = [
                    'library_id' => $settings['library_id'],
                    'region' => $settings['region'],
                    'zone_name' => $settings['zone_name'],
                    'access_key' => $settings['access_key'],
                    'service_type' => $settings['service'],
                    'upload_type' => $settings['upload_type'],
                    'common_use' => $settings['common_use'],
                    'token_authentication_key' => $settings['token_authentication_key'],
                    'hostname' => $settings['hostname'],
                ];

                if ($settings['service'] == 'stream') {
                    if (saasEnv('BUNNY_UPLOAD_TYPE') == "Direct") {
                        $bunnyStreamController = new BunnyStreamController();
                        $result = $bunnyStreamController->uploadFileToBunny($request->bunny);
                        if ($result['status']) {
                            $bunny_lesson_data['video_id'] = $result['path'];
                            $time = Carbon::now()->addDay(1)->unix();
                            $url = 'https://iframe.mediadelivery.net/embed/' . $settings['library_id'] . '/' . $result['path'];
                            $sha256 = hash('sha256', $settings['token_authentication_key'] . $result['path'] . $time);
                            $url .= "?token=" . $sha256 . '&expires=' . $time . '&autoplay=true&preload=true';
                            $lesson->video_url = $url;
                        } else {
                            $lesson->video_url = null;
                            // Toastr::error($result['path']->getOriginalContent()['message']);
                        }
                    } else {
                        $time = Carbon::now()->addDay(1)->unix();
                        $url = 'https://iframe.mediadelivery.net/embed/' . $settings['library_id'] . '/' . $request->bunny;
                        $sha256 = hash('sha256', $settings['token_authentication_key'] . $request->bunny . $time);
                        $url .= "?token=" . $sha256 . '&expires=' . $time . '&autoplay=true&preload=true';
                        $lesson->video_url = $url;
                        $bunny_lesson_data['video_id'] = $request->bunny;
                    }
                } elseif ($settings['service'] == 'storage') {
                    if (saasEnv('BUNNY_UPLOAD_TYPE') == "Direct") {
                        $bunnyStreamController = new BunnyStreamController();
                        $result = $bunnyStreamController->uploadFileToBunny($request->bunny);
                        if ($result['status']) {
                            $time = Carbon::now()->addDay(1)->unix();
                            $path = "https://" . $settings['zone_name'] . ".b-cdn.net/" . $result['path'];
                            $url = $bunnyStreamController->sign_bcdn_url($path, $settings['token_authentication_key'], $time);
                            $lesson->video_url = $url;
                            $bunny_lesson_data['name'] = $result['path'];
                        }
                    } else {
                        $bunnyStreamController = new BunnyStreamController();
                        $time = Carbon::now()->addDay(1)->unix();
                        $path = "https://" . $settings['zone_name'] . ".b-cdn.net/" . $request->bunny;
                        $url = $bunnyStreamController->sign_bcdn_url($path, $settings['token_authentication_key'], $time);
                        $lesson->video_url = $url;
                        $bunny_lesson_data['name'] = $request->bunny;
                    }
                }
            } elseif ($request->get('host') == "VdoCipher") {
                $lesson->video_url = $request->vdocipher;
            } elseif ($request->get('host') == "Youtube" || $request->get('host') == "URL") {
                $lesson->video_url = $request->video_url;
            } elseif ($request->get('host') == "Iframe") {
                $lesson->video_url = $request->iframe_url;
            } elseif ($request->get('host') == "Self") {
                $file = $this->getPublicPathFromServerId($request->get('file'));
                if (empty($file)) {
                    return response()->json(['message' => trans('courses.File is invalid')]);
                }
                $extension = pathinfo(base_path($file), PATHINFO_EXTENSION);
                if (!in_array(strtolower($extension), ['mp4', 'webm', 'ogg'])) {
                    return response()->json(['message' => trans('courses.File is invalid')]);
                }
                $lesson->video_url = $file;
            } elseif ($request->get('host') == "Storage") {
                $lesson->video_url = null;
                $lesson->save();

                if ($request->video) {
                    $lesson->video_url = $this->generateLink($request->video, $lesson->id, get_class($lesson), 'video_url');
                    $lesson->save();
                }
            } elseif ($request->get('host') == "AmazonS3") {
                $lesson->video_url = $this->getPublicPathFromServerId($request->get('file'), 's3');
            } elseif ($request->get('host') == "BunnyStorage" && isModuleActive('BunnyStorage')) {
                $settings = $this->getSettings();

                $bunny_lesson_data = [
                    'library_id' => $settings['library_id'],
                    'region' => $settings['region'],
                    'zone_name' => $settings['zone_name'],
                    'access_key' => $settings['access_key'],
                    'service_type' => $settings['service'],
                    'upload_type' => $settings['upload_type'],
                    'common_use' => $settings['common_use'],
                    'token_authentication_key' => $settings['token_authentication_key'],
                    'hostname' => $settings['hostname'],
                ];

                if ($settings['service'] == 'stream') {
                    if (saasEnv('BUNNY_UPLOAD_TYPE') == "Direct") {
                        $bunnyStreamController = new BunnyStreamController();
                        $result = $bunnyStreamController->uploadFileToBunny($request->bunny);
                        if ($result['status']) {
                            $bunny_lesson_data['video_id'] = $result['path'];
                            $time = Carbon::now()->addDay(1)->unix();
                            $url = 'https://iframe.mediadelivery.net/embed/' . $settings['library_id'] . '/' . $result['path'];
                            $sha256 = hash('sha256', $settings['token_authentication_key'] . $result['path'] . $time);
                            $url .= "?token=" . $sha256 . '&expires=' . $time . '&autoplay=true&preload=true';
                            $lesson->video_url = $url;
                        } else {
                            $lesson->video_url = null;
                            // Toastr::error($result['path']->getOriginalContent()['message']);
                        }
                    } else {
                        $time = Carbon::now()->addDay(1)->unix();
                        $url = 'https://iframe.mediadelivery.net/embed/' . $settings['library_id'] . '/' . $request->bunny;
                        $sha256 = hash('sha256', $settings['token_authentication_key'] . $request->bunny . $time);
                        $url .= "?token=" . $sha256 . '&expires=' . $time . '&autoplay=true&preload=true';
                        $lesson->video_url = $url;
                        $bunny_lesson_data['video_id'] = $request->bunny;
                    }
                } elseif ($settings['service'] == 'storage') {
                    if (saasEnv('BUNNY_UPLOAD_TYPE') == "Direct") {
                        $bunnyStreamController = new BunnyStreamController();
                        $result = $bunnyStreamController->uploadFileToBunny($request->bunny);
                        if ($result['status']) {
                            $time = Carbon::now()->addDay(1)->unix();
                            $path = "https://" . $settings['zone_name'] . ".b-cdn.net/" . $result['path'];
                            $url = $bunnyStreamController->sign_bcdn_url($path, $settings['token_authentication_key'], $time);
                            $lesson->video_url = $url;
                            $bunny_lesson_data['name'] = $result['path'];
                        } else {
                            // Toastr::error($result['path']->getOriginalContent()['message']);
                        }
                    } else {
                        $bunnyStreamController = new BunnyStreamController();
                        $time = Carbon::now()->addDay(1)->unix();
                        $path = "https://" . $settings['zone_name'] . ".b-cdn.net/" . $request->bunny;
                        $url = $bunnyStreamController->sign_bcdn_url($path, $settings['token_authentication_key'], $time);
                        $lesson->video_url = $url;
                        $bunny_lesson_data['name'] = $request->bunny;
                    }
                }
            } elseif ($request->get('host') == "VdoCipher") {
                $vdoCipher = new VdoCipherController();
                $lesson->video_url = $vdoCipher->uploadToVdoCipher($request->get('file'));
            } elseif ($request->get('host') == "SCORM") {
                $scorm = new SCORMController();
                $serverFile = $this->getPublicPathWithFileNameFromServerId($request->get('file'));
                if (empty($serverFile)) {
                    // Toastr::error(trans('courses.File is invalid'), trans('common.Error'));
                    return response()->json(['message' => trans('courses.File is invalid')]);
                }
                $result = $scorm->getScormUrl($serverFile['link'], $request->get('host'));
                if ($result) {
                    $lesson->video_url = $result['url'] ?? "";
                    $lesson->scorm_title = $result['title'] ?? '';
                    $lesson->scorm_version = $result['version'] ?? '';
                    $lesson->scorm_identifier = $result['identifier'] ?? '';
                } else {
                    // Toastr::error(trans('courses.Scorm field is invalid'), trans('common.Error'));
                    return response()->json(['message' => trans('courses.Scorm field is invalid')]);
                }
            } elseif ($request->get('host') == "SCORM-AwsS3") {
                $scorm = new SCORMController();
                $serverFile = $this->getPublicPathWithFileNameFromServerId($request->get('file'));
                if (empty($serverFile)) {
                    // Toastr::error(trans('courses.Scorm field is invalid'), trans('common.Error'));
                    return response()->json(['message' => trans('courses.Scorm field is invalid')]);
                }
                $result = $scorm->getScormUrl($serverFile['link'], $request->get('host'));
                if ($result) {
                    $lesson->video_url = $result['url'] ?? '';
                    $lesson->scorm_title = $result['title'] ?? '';
                    $lesson->scorm_version = $result['version'] ?? '';
                    $lesson->scorm_identifier = $result['identifier'] ?? '';
                } else {
                    // Toastr::error(trans('courses.Scorm field is invalid'), trans('common.Error'));
                    return response()->json(['message' => trans('courses.Scorm field is invalid')]);
                }
            } elseif ($request->get('host') == "H5P") {
                $h5p = new H5PController();
                $serverFile = $this->getPublicPathWithFileNameFromServerId($request->get('file'));
                if (empty($serverFile)) {
                    // Toastr::error(trans('courses.Scorm field is invalid'), trans('common.Error'));
                    return response()->json(['message' => trans('courses.Scorm field is invalid')]);
                }
                $result = $h5p->getH5PUrl($serverFile ? $serverFile['link'] : null, $request->get('host'), $request);
                if ($result) {
                    $lesson->video_url = $result->path;
                    $lesson->h5p_id = $result->id;
                } else {
                    // Toastr::error(trans('courses.H5P field is invalid'), trans('common.Error'));
                    return response()->json(['message' => trans('courses.H5P field is invalid')]);
                }
            } elseif ($request->get('host') == "XAPI") {
                $xapi = new XAPIController();
                $serverFile = $this->getPublicPathWithFileNameFromServerId($request->get('file'));
                if (empty($serverFile)) {
                    // Toastr::error(trans('courses.Scorm field is invalid'), trans('common.Error'));
                    return response()->json(['message' => trans('courses.Scorm field is invalid')]);
                }
                $result = $xapi->getXAPIUrl($serverFile['link'], $request->get('host'));
                if ($result) {
                    $lesson->video_url = $result['url'];
                } else {
                    // Toastr::error(trans('courses.XAPI field is invalid'), trans('common.Error'));
                    return response()->json(['message' => trans('courses.XAPI field is invalid')]);
                }
            } elseif ($request->get('host') == "XAPI-AwsS3") {
                $xapi = new XAPIController();
                $serverFile = $this->getPublicPathWithFileNameFromServerId($request->get('file'));
                if (empty($serverFile)) {
                    // Toastr::error(trans('courses.Scorm field is invalid'), trans('common.Error'));
                    return response()->json(['message' => trans('courses.Scorm field is invalid')]);
                }
                $result = $xapi->getXAPIUrl($serverFile['link'], $request->get('host'));
                if ($result) {
                    $lesson->video_url = $result['url'] ?? '';
                } else {
                    // Toastr::error(trans('courses.XAPI field is invalid'), trans('common.Error'));
                    return response()->json(['message' => trans('courses.XAPI field is invalid')]);
                }
            } elseif (in_array($request->get('host'), ['Zip', 'PowerPoint', 'Excel', 'Text', 'Word', 'PDF', 'Image'])
                /* $request->get('host') == "Zip"
                || $request->get('host') == "PowerPoint"
                || $request->get('host') == "Excel"
                || $request->get('host') == "Text"
                || $request->get('host') == "Word"
                || $request->get('host') == "PDF"
                || $request->get('host') == "Image" */) {
                $file = $this->getPublicPathFromServerId($request->get('file'));
                if (empty($file)) {
                    // Toastr::error(trans('courses.File is not uploaded'), trans('common.Error'));
                    return response()->json(['message' => trans('courses.File is not uploaded')]);
                }
                $extension = pathinfo(base_path($file), PATHINFO_EXTENSION);
                if ($request->get('host') == "Zip" && strtolower($extension) != 'zip') {
                    // Toastr::error(trans('courses.Invalid Zip file'), trans('common.Error'));
                    return response()->json(['message' => trans('courses.Invalid Zip file')]);
                } elseif ($request->get('host') == "PowerPoint" && !in_array(strtolower($extension), ['ppt', 'pptx'])) {
                    // Toastr::error(trans('courses.Invalid PowerPoint file'), trans('common.Error'));
                    return response()->json(['message' => trans('courses.Invalid PowerPoint file')]);
                } elseif ($request->get('host') == "Excel" && !in_array(strtolower($extension), ['xlsx', 'xls'])) {
                    // Toastr::error(trans('courses.Invalid Excel file'), trans('common.Error'));
                    return response()->json(['message' => trans('courses.Invalid Excel file')]);
                } elseif ($request->get('host') == "Text" && !in_array(strtolower($extension), ['txt'])) {
                    // Toastr::error(trans('courses.Invalid Excel file'), trans('common.Error'));
                    return response()->json(['message' => trans('courses.Invalid Excel file')]);
                } elseif ($request->get('host') == "Word" && !in_array(strtolower($extension), ['doc', 'docx'])) {
                    // Toastr::error(trans('courses.Invalid Word file'), trans('common.Error'));
                    return response()->json(['message' => trans('courses.Invalid Word file')]);
                } elseif ($request->get('host') == "PDF" && !in_array(strtolower($extension), ['pdf'])) {
                    // Toastr::error(trans('courses.Invalid PDF file'), trans('common.Error'));
                    return response()->json(['message' => trans('courses.Invalid PDF file')]);
                } elseif ($request->get('host') == "Image" && !in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'svg'])) {
                    // Toastr::error(trans('courses.Invalid Image file'), trans('common.Error'));
                    return response()->json(['message' => trans('courses.Invalid Image file')]);
                }

                $lesson->video_url = $file;
            } elseif ($request->get('host') == "GoogleDrive") {
                if (empty(auth()->user()->googleToken)) {
                    // Toastr::error(trans('setting.Google Drive login is required'), trans('common.Error'));
                    return response()->json(['message' => trans('courses.Google Drive login is required')]);
                }
                $id = null;
                $url = $this->getPublicPathFromServerId($request->get('file'), 'local');
                if ($url) {
                    $file = $this->storeFileInGDrive(base_path($url), null);
                    if (isset($file->id)) {
                        $id = $file->id;
                    }
                    if (File::exists(base_path($url))) {
                        File::delete(base_path($url));
                    }
                }

                $lesson->video_url = $id;
            } else {
                $lesson->video_url = null;
            }
            $lesson->host = $request->host;


            if ($lesson->video_url != null && saasPlanCheck('upload_limit', $lesson->video_url)) {
                // Toastr::error('You have reached upload limit', trans('common.Failed'));
                return response()->json(['message' => trans('api.You have reached upload limit')]);
            }

            $lesson->duration = $request->duration;
            $lesson->is_lock = (int)$request->is_lock;
            $lesson->save();


            $bunny_lesson_data['lesson_id'] = $lesson->id;

            if (isModuleActive('BunnyStorage') && isset($bunny_lesson_data)) {
                BunnyLesson::create($bunny_lesson_data);
            }

            $ignoreHost = ['SCORM', 'SCORM-AwsS3', 'XAPI', 'XAPI-AwsS3'];
            if (in_array($lesson->host, $ignoreHost)) {
                $size = $serverFile['size'] ?? 0;
            } elseif (!empty($lesson->video_url) && selfHosted($lesson->host)) {
                $size = file_exists(base_path($lesson->video_url)) ? filesize($lesson->video_url) ?? 0 : 0;
            } else {
                $size = 0;
            }
            /* if (isModuleActive('Org')) {
                $lesson->file_id = null;
                $lesson->org_material_id = $this->getMaterialId($lesson->video_url);
            } else {
            } */
            $lesson->file_id = $this->addFile([
                'lesson_id' => $lesson->id,
                'title' => $lesson->name,
                'link' => $lesson->video_url,
                'version' => 1,
                'size' => $size,
                'type' => $lesson->host,
                'scorm_title' => $lesson->scorm_title,
                'scorm_version' => $lesson->scorm_version,
                'scorm_identifier' => $lesson->scorm_identifier,
            ]);

            $lesson->save();

            try {
                if (isset($course->enrollUsers) && !empty($course->enrollUsers)) {
                    foreach ($course->enrollUsers as $user) {
                        if (UserMobileNotificationSetup('Course_Lesson_Added', $user) && !empty($user->device_token)) {
                            send_mobile_notification($user, 'Course_Lesson_Added', [
                                'time' => Carbon::now()->format('d-M-Y, g:i A'),
                                'course' => $course->title,
                                'chapter' => $chapter->name,
                                'lesson' => $lesson->name,
                            ]);
                        }
                        if (UserEmailNotificationSetup('Course_Lesson_Added', $user)) {
                            SendGeneralEmail::dispatch($user, 'Course_Lesson_Added', [
                                'time' => Carbon::now()->format('d-M-Y, g:i A'),
                                'course' => $course->title,
                                'chapter' => $chapter->name,
                                'lesson' => $lesson->name,
                            ]);
                        }
                        if (UserBrowserNotificationSetup('Course_Lesson_Added', $user)) {
                            send_browser_notification(
                                $user,
                                $type = 'Course_Lesson_Added',
                                $shortcodes = [
                                    'time' => Carbon::now()->format('d-M-Y, g:i A'),
                                    'course' => $course->title,
                                    'chapter' => $chapter->name,
                                    'lesson' => $lesson->name,
                                ],
                                trans('common.View'), //actionText
                                courseDetailsUrl(@$course->id, @$course->type, @$course->slug), //actionUrl
                                'lesson',
                                $course->id
                            );
                        }

                        if (UserSmsNotificationSetup('Course_Lesson_Added', $user)) {
                            send_sms_notification($user, $type = 'Course_Lesson_Added', $shortcodes = [
                                'time' => Carbon::now()->format('d-M-Y, g:i A'),
                                'course' => $course->title,
                                'chapter' => $chapter->name,
                                'lesson' => $lesson->name,
                            ]);
                        }
                    }
                }
                $courseUser = $course->user;
                if (UserMobileNotificationSetup('Course_Lesson_Added', $courseUser) && !empty($courseUser->device_token)) {
                    send_mobile_notification($courseUser, 'Course_Lesson_Added', [
                        'time' => Carbon::now()->format('d-M-Y, g:i A'),
                        'course' => $course->title,
                        'chapter' => $chapter->name,
                        'lesson' => $lesson->name,
                    ]);
                }
                if (UserEmailNotificationSetup('Course_Lesson_Added', $courseUser)) {
                    SendGeneralEmail::dispatch($courseUser, 'Course_Lesson_Added', [
                        'time' => Carbon::now()->format('d-M-Y, g:i A'),
                        'course' => $course->title,
                        'chapter' => $chapter->name,
                        'lesson' => $lesson->name,
                    ]);
                }
                if (UserBrowserNotificationSetup('Course_Lesson_Added', $courseUser)) {
                    send_browser_notification(
                        $courseUser,
                        $type = 'Course_Lesson_Added',
                        $shortcodes = [
                            'time' => Carbon::now()->format('d-M-Y, g:i A'),
                            'course' => $course->title,
                            'chapter' => $chapter->name,
                            'lesson' => $lesson->name,
                        ],
                        trans('common.View'), //actionText
                        courseDetailsUrl(@$course->id, @$course->type, @$course->slug), //actionUrl
                        'lesson',
                        $course->id
                    );
                }

                if (UserSmsNotificationSetup('Course_Lesson_Added', $courseUser)) {
                    send_sms_notification($courseUser, $type = 'Course_Lesson_Added', $shortcodes = [
                        'time' => Carbon::now()->format('d-M-Y, g:i A'),
                        'course' => $course->title,
                        'chapter' => $chapter->name,
                        'lesson' => $lesson->name,
                    ]);
                }
            } catch (\Exception $e) {
                //throw $th;
            }

            (new CourseSettingController)->updateTotalCountForCourse($course);
        }
        return response()->json(true);
    }

    public function lessons(object $request): object
    {
        $lessons = Lesson::where('course_id', $request->course_id)
            ->when($request->chapter_id, function ($lesson) use ($request) {
                $lesson->where('chapter_id', $request->chapter_id);
            })
            ->when($request->quiz_id, function ($lesson) use ($request) {
                $lesson->where('quiz_id', $request->quiz_id);
            })->get();

        return LessonListResource::collection($lessons);
    }

    public function lessonDetail(object $request): object
    {
        $lesson = Lesson::where('is_quiz', 0)
            ->where('is_assignment', 0)
            ->where('course_id', $request->course_id)
            ->where('chapter_id', $request->chapter_id)
            ->find($request->lesson_id);

        return new LessonListResource($lesson);
    }

    public function hosts(): array
    {
        return [
            'Youtube'       => 'Youtube',
            'Vimeo'         => 'Vimeo',
            'VdoCipher'     => 'VdoCipher',
            'Self'          => 'Self',
            'URL'           => 'Video URL',
            'Iframe'        => 'Iframe embed',
            'Image'         => 'Image',
            'PDF'           => 'PDF File',
            'Word'          => 'Word File',
            'Excel'         => 'Excel File',
            'Text'          => 'Text File',
            'Zip'           => 'Zip File',
            'GoogleDrive'   => 'Google Drive',
            'PowerPoint'    => 'Power Point File',
            'BunnyStorage'  => 'Bunny Storage',
            'SCORM'         => 'SCORM Self',
            'H5P'           => 'H5P',
            'XAPI'          => 'XAPI Self',
            'Storage'       => 'Storage',
        ];
    }

    public function privacies(): object
    {
        $privacies = [
            [
                'key'   => 0,
                'name'  => 'Unlock',
            ],
            [
                'key'   => 1,
                'name'  => 'Locked'
            ]
        ];

        $response = [
            'success'   => true,
            'data'      => $privacies,
            'message'   => trans('api.Operation successful')
        ];

        return response()->json($response);
    }

    /* public function updateLesson(object $request): object
    {
        $request->validate([
            'lesson_id'     => 'required|exists:lessons,id',
            'name'          => 'required|string',
            'chapter_id'    => 'required|exists:chapters,id',
            'duration'      => 'required|numeric',
            'course_id'     => 'required|exists:courses,id',
            'video_url'     => 'required|url:http,https'
        ]);

        $privacy = $request->privacy == 'locked' || $request->privacy ==  ucwords('locked') || $request->privacy ==  strtoupper('locked') ? 1 : 0;

        $user = auth()->user();
        if ($user->role_id == 2) {
            $course = Course::where('id', $request->course_id)->where('user_id', auth()->id())->first();
        } else {
            $course = Course::where('id', $request->course_id)->first();
        }

        $chapter = Chapter::find($request->chapter_id);

        if (!$course && !$chapter) {
            $response = [
                'success'   => false,
                'data'      => null,
                'message'   => trans('api.Operation failed')
            ];
        } else {
            trans('lang.Lesson') . ' ' . trans('lang.Added') . ' ' . trans('lang.Successfully');

            $lesson = Lesson::find($request->lesson_id);

            $lesson->update([
                'course_id'     => $request->course_id,
                'chapter_id'    => $request->chapter_id,
                'name'          => $request->name,
                'description'   => $request->description,
                'video_url'     => $request->video_url,
                'host'          => $request->host,
                'duration'      => $request->duration,
                'is_lock'       => $privacy
            ]);

            try {
                if (isset($course->enrollUsers) && !empty($course->enrollUsers)) {
                    foreach ($course->enrollUsers as $user) {
                        if (UserEmailNotificationSetup('Course_Lesson_Added', $user)) {
                            SendGeneralEmail::dispatch($user, 'Course_Lesson_Added', [
                                'time'      => Carbon::now()->format('d-M-Y, g:i A'),
                                'course'    => $course->title,
                                'chapter'   => $chapter->name,
                                'lesson'    => $lesson->name,
                            ]);
                        }
                        if (UserBrowserNotificationSetup('Course_Lesson_Added', $user)) {

                            send_browser_notification(
                                $user,
                                'Course_Lesson_Added',
                                [
                                    'time'      => Carbon::now()->format('d-M-Y, g:i A'),
                                    'course'    => $course->title,
                                    'chapter'   => $chapter->name,
                                    'lesson'    => $lesson->name,
                                ],
                                trans('common.View'),
                                courseDetailsUrl($course->id, $course->type, $course->slug),
                            );
                        }

                        if (UserMobileNotificationSetup('Course_Lesson_Added', $user) && !empty($user->device_token)) {
                            send_mobile_notification($user, 'Course_Lesson_Added', [
                                'time'      => Carbon::now()->format('d-M-Y, g:i A'),
                                'course'    => $course->title,
                                'chapter'   => $chapter->name,
                                'lesson'    => $lesson->name,
                            ]);
                        }

                        if (UserSmsNotificationSetup('Course_Lesson_Added', $user)) {

                            send_sms_notification($user, 'Course_Lesson_Added', [
                                'time'      => Carbon::now()->format('d-M-Y, g:i A'),
                                'course'    => $course->title,
                                'chapter'   => $chapter->name,
                                'lesson'    => $lesson->name,
                            ]);
                        }
                    }
                }
            } catch (\Exception $e) {
                // return response()->json(['message' => $e->getMessage()]);
            }
            $data = [
                'course_id'         => $lesson->course_id,
                'chapter_id'        => $lesson->chapter_id,
                'chapter_name'      => $lesson->name,
                'description'       => $lesson->description,
                'video_url'         => $lesson->video_url,
                'chapter_host'      => $lesson->host,
                'chapter_duration'  => $lesson->duration,
                'is_lock'           => (bool)$lesson->is_lock
            ];
            $response = [
                'success'   => true,
                'data'      => $data,
                'message'   => trans('api.Operation successful')
            ];
        }

        return response()->json($response);
    } */

    public function updateLesson(object $request): object
    {
        $user = auth()->user();
        if ($user->role_id == 2) {
            $course = Course::where('user_id', auth()->id())->find($request->course_id);
        } else {
            $course = Course::find($request->course_id);
        }
        $chapter = Chapter::where('course_id', $course->id)->find($request->chapter_id);

        if (isset($course) && isset($chapter)) {
            $lesson = Lesson::find($request->lesson_id);
            $lesson->course_id = $request->course_id;
            $lesson->chapter_id = $request->chapter_id;
            $lesson->name = $request->name;
            $lesson->description = $request->description;
            $lesson->host = $request->host;
            if ($request->get('host') == "Vimeo") {
                if (config('vimeo.connections.main.upload_type') == "Direct") {
                    $vimeoController = new VimeoController();
                    $lesson->video_url = $vimeoController->uploadFileIntoVimeo(md5(time()), $request->vimeo);
                } else {
                    $lesson->video_url = $request->vimeo;
                }
            } elseif ($request->get('host') == "BunnyStorage" && isModuleActive('BunnyStorage')) {
                $settings = $this->getSettings();

                $bunny_lesson_data = [
                    'library_id' => $settings['library_id'],
                    'region' => $settings['region'],
                    'zone_name' => $settings['zone_name'],
                    'access_key' => $settings['access_key'],
                    'service_type' => $settings['service'],
                    'upload_type' => $settings['upload_type'],
                    'common_use' => $settings['common_use'],
                    'token_authentication_key' => $settings['token_authentication_key'],
                    'hostname' => $settings['hostname'],
                    'lesson_id' => $lesson->id,
                ];

                if ($settings['service'] == 'stream') {
                    if (saasEnv('BUNNY_UPLOAD_TYPE') == "Direct") {
                        $bunnyStreamController = new BunnyStreamController();
                        $result = $bunnyStreamController->uploadFileToBunny($request->bunny);
                        if ($result['status']) {
                            $bunny_lesson_data['video_id'] = $result['path'];
                            $time = Carbon::now()->addDay(1)->unix();
                            $url = 'https://iframe.mediadelivery.net/embed/' . $settings['library_id'] . '/' . $result['path'];
                            $sha256 = hash('sha256', $settings['token_authentication_key'] . $result['path'] . $time);
                            $url .= "?token=" . $sha256 . '&expires=' . $time . '&autoplay=true&preload=true';
                            $lesson->video_url = $url;
                        } else {
                            $lesson->video_url = null;
                            // Toastr::error($result['path']->getOriginalContent()['message']);
                        }
                    } else {
                        $time = Carbon::now()->addDay(1)->unix();
                        $url = 'https://iframe.mediadelivery.net/embed/' . $settings['library_id'] . '/' . $request->bunny;
                        $sha256 = hash('sha256', $settings['token_authentication_key'] . $request->bunny . $time);
                        $url .= "?token=" . $sha256 . '&expires=' . $time . '&autoplay=true&preload=true';
                        $lesson->video_url = $url;
                        $bunny_lesson_data['video_id'] = $request->bunny;
                    }
                } elseif ($settings['service'] == 'storage') {
                    if (saasEnv('BUNNY_UPLOAD_TYPE') == "Direct") {
                        $bunnyStreamController = new BunnyStreamController();
                        $result = $bunnyStreamController->uploadFileToBunny($request->bunny);
                        if ($result['status']) {
                            $time = Carbon::now()->addDay(1)->unix();
                            $path = "https://" . $settings['zone_name'] . ".b-cdn.net/" . $result['path'];
                            $url = $bunnyStreamController->sign_bcdn_url($path, $settings['token_authentication_key'], $time);
                            $lesson->video_url = $url;
                            $bunny_lesson_data['name'] = $result['path'];
                        }
                    } else {
                        $bunnyStreamController = new BunnyStreamController();
                        $time = Carbon::now()->addDay(1)->unix();
                        $path = "https://" . $settings['zone_name'] . ".b-cdn.net/" . $request->bunny;
                        $url = $bunnyStreamController->sign_bcdn_url($path, $settings['token_authentication_key'], $time);
                        $lesson->video_url = $url;
                        $bunny_lesson_data['name'] = $request->bunny;
                    }
                }

                $exist_bunny = BunnyLesson::where('lesson_id', $lesson->id)->first();
                if ($exist_bunny) {
                    $exist_bunny->update($bunny_lesson_data);
                } else {
                    BunnyLesson::create($bunny_lesson_data);
                }
            } elseif ($request->get('host') == "VdoCipher") {
                $lesson->video_url = $request->vdocipher;
            } elseif ($request->get('host') == "Youtube" || $request->get('host') == "URL") {
                $lesson->video_url = $request->video_url;
            } elseif ($request->get('host') == "Iframe") {
                $lesson->video_url = $request->iframe_url;
            } elseif ($request->get('host') == "Self") {

                $file = $this->getPublicPathFromServerId($request->get('file'));
                if (empty($file)) {
                    // Toastr::error(trans('courses.File is not uploaded'), trans('common.Error'));
                    return response()->json(['message' => trans('courses.File is not uploaded')]);
                }
                $extension = pathinfo(base_path($file), PATHINFO_EXTENSION);

                if (!in_array(strtolower($extension), ['mp4', 'webm', 'ogg'])) {
                    // Toastr::error(trans('courses.Invalid Video file'), trans('common.Error'));
                    return response()->json(['message' => trans('courses.Invalid Video file')]);
                }
                $lesson->video_url = $file;
            } elseif ($request->get('host') == "Storage") {
                $lesson->video_url = null;
                $lesson->save();
                $this->removeLink($lesson->id, get_class($lesson));

                if ($request->video) {
                    $lesson->video_url = $this->generateLink($request->video, $lesson->id, get_class($lesson), 'video_url');
                    $lesson->save();
                }
            } elseif ($request->get('host') == "AmazonS3") {
                if (!empty($request->get('file'))) {
                    $lesson->video_url = $this->getPublicPathFromServerId($request->get('file'), 's3');
                }
            } elseif ($request->get('host') == "SCORM") {
                if (!empty($request->get('file'))) {
                    $serverFile = $this->getPublicPathWithFileNameFromServerId($request->get('file'), 'local');

                    $scorm = new SCORMController();
                    $result = $scorm->getScormUrl($serverFile['link'], $request->get('host'));
                    if ($result) {
                        $lesson->video_url = $result['url'];
                        $lesson->scorm_title = $result['title'];
                        $lesson->scorm_version = $result['version'];
                        $lesson->scorm_identifier = $result['identifier'];
                    } else {
                        // Toastr::error(trans('courses.Scorm field is invalid'), trans('common.Error'));
                        return response()->json(['message' => trans('courses.Scorm field is invalid')]);
                    }
                }
            } elseif ($request->get('host') == "SCORM-AwsS3") {
                if (!empty($request->get('file'))) {
                    $scorm = new SCORMController();
                    $serverFile = $this->getPublicPathWithFileNameFromServerId($request->get('file'));
                    $result = $scorm->getScormUrl($serverFile['link'], $request->get('host'));

                    if ($result) {
                        $lesson->video_url = $result['url'];
                        $lesson->scorm_title = $result['title'];
                        $lesson->scorm_version = $result['version'];
                        $lesson->scorm_identifier = $result['identifier'];
                    } else {
                        // Toastr::error(trans('courses.Scorm field is invalid'), trans('common.Error'));
                        return response()->json(['message' => trans('courses.Scorm field is invalid')]);
                    }
                }
            } elseif ($request->get('host') == "H5P") {
                $h5p = new H5PController();
                $serverFile = $this->getPublicPathWithFileNameFromServerId($request->get('file'));
                $result = $h5p->getH5PUrl($serverFile ? $serverFile['link'] : null, $request->get('host'), $request);

                if ($result) {
                    $lesson->video_url = $result->path;
                    $lesson->h5p_id = $result->id;
                } else {
                    // Toastr::error(trans('courses.H5P field is invalid'), trans('common.Error'));
                    return response()->json(['message' => trans('courses.H5P field is invalid')]);
                }
            } elseif ($request->get('host') == "XAPI") {
                $xapi = new XAPIController();
                $serverFile = $this->getPublicPathWithFileNameFromServerId($request->get('file'));
                $result = $xapi->getXAPIUrl($serverFile['link'], $request->get('host'));
                if ($result) {
                    $lesson->video_url = $result['url'];
                } else {
                    // Toastr::error(trans('courses.XAPI field is invalid'), trans('common.Error'));
                    return response()->json(['message' => trans('courses.XAPI field is invalid')]);
                }
            } elseif ($request->get('host') == "XAPI-AwsS3") {
                $xapi = new XAPIController();
                $serverFile = $this->getPublicPathWithFileNameFromServerId($request->get('file'));
                $result = $xapi->getXAPIUrl($serverFile['link'], $request->get('host'));
                if ($result) {
                    $lesson->video_url = $result['url'];
                } else {
                    // Toastr::error(trans('courses.XAPI field is invalid'), trans('common.Error'));
                    return response()->json(['message' => trans('courses.XAPI field is invalid')]);
                }
            } elseif (
                in_array($request->get('host'), ['Zip', 'PowerPoint', 'Excel', 'Text', 'Word', 'PDF', 'Image'])
                /* $request->get('host') == "Zip"
                || $request->get('host') == "PowerPoint"
                || $request->get('host') == "Excel"
                || $request->get('host') == "Text"
                || $request->get('host') == "Word"
                || $request->get('host') == "PDF"
                || $request->get('host') == "Image" */
            ) {
                $file = $this->getPublicPathFromServerId($request->get('file'));

                if ($file) {
                    $extension = pathinfo(base_path($file), PATHINFO_EXTENSION);
                    if ($request->get('host') == "Zip" && strtolower($extension) != 'zip') {
                        // Toastr::error(trans('courses.Invalid Zip file'), trans('common.Error'));
                        return response()->json(['message' => trans('courses.Invalid Zip file')]);
                    } elseif ($request->get('host') == "PowerPoint" && !in_array(strtolower($extension), ['ppt', 'pptx'])) {
                        // Toastr::error(trans('courses.Invalid PowerPoint file'), trans('common.Error'));
                        return response()->json(['message' => trans('courses.Invalid PowerPoint file')]);
                    } elseif ($request->get('host') == "Excel" && !in_array(strtolower($extension), ['xlsx', 'xls'])) {
                        // Toastr::error(trans('courses.Invalid Excel file'), trans('common.Error'));
                        return response()->json(['message' => trans('courses.Invalid Excel file')]);
                    } elseif ($request->get('host') == "Text" && !in_array(strtolower($extension), ['txt'])) {
                        // Toastr::error(trans('courses.Invalid Excel file'), trans('common.Error'));
                        return response()->json(['message' => trans('courses.Invalid Excel file')]);
                    } elseif ($request->get('host') == "Word" && !in_array(strtolower($extension), ['doc', 'docx'])) {
                        // Toastr::error(trans('courses.Invalid Word file'), trans('common.Error'));
                        return response()->json(['message' => trans('courses.Invalid Word file')]);
                    } elseif ($request->get('host') == "PDF" && !in_array(strtolower($extension), ['pdf'])) {
                        // Toastr::error(trans('courses.Invalid PDF file'), trans('common.Error'));
                        return response()->json(['message' => trans('courses.Invalid PDF file')]);
                    } elseif ($request->get('host') == "Image" && !in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'svg'])) {
                        // Toastr::error(trans('courses.Invalid Image file'), trans('common.Error'));
                        return response()->json(['message' => trans('courses.Invalid Image file')]);
                    }

                    $lesson->video_url = $file;
                }
            } elseif ($request->get('host') == "GoogleDrive") {
                if (empty(auth()->user()->googleToken)) {
                    // Toastr::error(trans('setting.Google Drive login is required'), trans('common.Error'));
                    return response()->json(['message' => trans('setting.Google Drive login is required')]);
                }
                $id = null;
                $url = $this->getPublicPathFromServerId($request->get('file'), 'local');
                if ($url) {
                    $file = $this->storeFileInGDrive(base_path($url));
                    if (isset($file->id)) {
                        $id = $file->id;
                    }
                    if (File::exists(base_path($url))) {
                        File::delete(base_path($url));
                    }
                }

                $lesson->video_url = $id;
            } else {
                $lesson->video_url = null;
            }
            $ignoreHost = ['SCORM', 'SCORM-AwsS3', 'XAPI', 'XAPI-AwsS3'];
            if (in_array($lesson->host, $ignoreHost)) {
                $size = $serverFile['size'] ?? 0;
            } elseif (!empty($lesson->video_url) && selfHosted($lesson->host)) {
                $size = file_exists(base_path($lesson->video_url)) ? filesize($lesson->video_url) ?? 0 : 0;
            } else {
                $size = 0;
            }
            /* if (isModuleActive('Org')) {
                $lesson->file_id = null;
                $lesson->org_material_id = $this->getMaterialId($lesson->video_url);
            } else {
            } */
            $lesson->file_id = $this->addFile([
                'lesson_id' => $lesson->id,
                'link' => $lesson->video_url,
                'title' => $lesson->name,
                'version' => count($lesson->files) + 1,
                'size' => $size,
                'type' => $lesson->host,
                'scorm_title' => $lesson->scorm_title,
                'scorm_version' => $lesson->scorm_version,
                'scorm_identifier' => $lesson->scorm_identifier,
            ]);

            $lesson->duration = $request->duration;
            $lesson->is_lock = (int)$request->is_lock;
            $lesson->update();

            $self_hosts = ['Self', 'Image', 'PDF', 'Word', 'Excel', 'Text', 'Zip', 'PowerPoint'];
            if (in_array($lesson->host, $self_hosts)) {
                $filesize = file_exists(base_path($lesson->video_url)) ? filesize($lesson->video_url) ?? 0 : 0;
                $filesize = round($filesize / 1024, 2); //KB
                if (isModuleActive('LmsSaas')) {
                    if (in_array($lesson->host, $self_hosts)) {
                        saasPlanManagement('upload_limit', 'create', $filesize);
                    }
                    if (in_array($lesson->host, $self_hosts) && $lesson->old_file_size != null) {
                        saasPlanManagement('upload_limit', 'delete', $lesson->old_file_size);
                    }
                }

                $lesson->old_file_size = $filesize;
                $lesson->file_size = $filesize;
                $lesson->update();
            }

            SendGeneralEmail::dispatch(auth()->user(), 'Course_Lesson_Added ', [
                'time' => Carbon::now()->format('d-M-Y ,g:i A'),
                'course' => $course->title,
                'chapter' => $chapter->name,
                'lesson' => $lesson->name,
            ]);
            (new CourseSettingController())->updateTotalCountForCourse($course);
        }
        return response()->json(true);
    }

    public function deleteLesson(object $request): void
    {
        $lesson = Lesson::where('course_id', $request->course_id)->where('chapter_id', $request->chapter_id)->find($request->lesson_id);

        if (auth()->user()->role_id == 2) {
            $course = Course::where('user_id', auth()->id())->find($lesson->course_id);
        } else {
            $course = Course::find($lesson->course_id);
        }

        if (isset($course)) {
            if ($lesson->is_assignment == 1) {
                $assignment = InfixAssignment::where('course_id', $course->id)->find($lesson->assignment_id);
                $assignment->delete();
            }
            $this->lessonFileDelete($lesson);

            if (isModuleActive('BunnyStorage')) {
                if ($lesson->bunnyLesson) {
                    $lesson->bunnyLesson->delete();
                }
            }
            $lesson->delete();
        }
    }

    private function lessonFileDelete($lesson): bool
    {
        try {
            $host = $lesson->host;
            if ($host == "SCORM") {
                $index = $lesson->video_url;
                if (!empty($index)) {
                    $new_path = str_replace("/public/uploads/scorm/", "", $index);
                    $folder = explode('/', $new_path)[0] ?? '';
                    $delete_dir = public_path() . "/uploads/scorm/" . $folder;

                    if (File::isDirectory($delete_dir)) {
                        File::deleteDirectory($delete_dir);
                    }
                }
            } elseif (in_array($host, ['Self', 'Image', 'PDF', 'Word', 'Excel', 'PowerPoint', 'Text', 'Zip'])) {
                $file = File::exists($lesson->video_url);

                if ($file) {
                    File::delete($lesson->video_url);
                }
            }
        } catch (\Exception $e) {
        }
        return true;
    }

    private function addFile($data): int
    {
        if (selfHosted($data['type'])) {
            $file = new LessonFile();
            $file->lesson_id = $data['lesson_id'];
            $file->link = $data['link'];
            $file->title = $data['title'];
            $file->version = $data['version'];
            $file->updated_by = auth()->id();
            $file->size = $data['size'] ?? '';
            $file->type = $data['type'];
            $file->scorm_title = $data['scorm_title'] ?? '';
            $file->scorm_version = $data['scorm_version'] ?? '';
            $file->scorm_identifier = $data['scorm_identifier'] ?? '';
            $file->save();
            return (int)$file->id;
        } else {
            return 0;
        }
    }

    /* private function getMaterialId($link)
    {
        $id = null;
        if (isModuleActive('Org')) {
            $file = OrgMaterialFile::where('link', $link)->first();
            if ($file) {
                $id = $file->material_id;
            }
        }
        return $id;
    } */
}
