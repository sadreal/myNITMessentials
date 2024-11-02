<?php

namespace App\Repositories\Eloquents;

use App\Models\UserDocument;
use App\Models\UserEducation;
use App\Models\UserExperience;
use App\Models\UserInstantMessage;
use App\Models\UserSkill;
use App\Repositories\Interfaces\AuthUserRepositoryInterface;
use App\Traits\Filepond;
use App\Traits\ImageStore;
use App\Traits\UploadMedia;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Setting\Repositories\MediaManagerRepository;

class AuthUserRepository implements AuthUserRepositoryInterface
{
    use UploadMedia, Filepond, ImageStore;
    private $mediaManagerRepository;
    public function __construct(MediaManagerRepository $mediaManagerRepository)
    {
        $this->mediaManagerRepository = $mediaManagerRepository;
    }
    public function basicInfoUpdate(object $request): void
    {
        DB::transaction(function () use ($request) {
            $user = User::find(auth()->id());

            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->currency_id = $request->currency_id;
            $user->language_id = $request->language_id;
            $user->save();

            $user->userInfo()->updateOrCreate(
                ['user_id' => auth()->id()],
                [
                    'user_id' => auth()->id(),
                    'timezone_id' => $request->timezone_id,
                ]
            );

            if ($user->role_id == 3) {
                $rules = [
                    'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:20480',
                    'cover_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:20480',
                ];
                $request->validate($rules, validationMessage($rules));
            }

            if ($user->role_id == 3) {
                if ($request->file('profile_image')) {
                    $image = $this->mediaManagerRepository->store($request->profile_image);
                    $profile_url = $this->generateLink($image->id, $user->id, get_class($user), 'image');
                    if ($user->image) {
                        $this->deleteImage($user->image);
                    }

                    $user->image = $profile_url;
                    $user->save();
                }

                if ($request->file('cover_photo')) {
                    $image = $this->mediaManagerRepository->store($request->cover_photo);
                    $cover_url = $this->generateLink($image->id, $user->id, get_class($user), 'cover_photo');
                    if ($user->userInfo && $user->userInfo->cover_photo) {
                        $this->deleteImage($user->userInfo->cover_photo);
                    }
                    $user->userInfo()->updateOrCreate(
                        ['user_id' => auth()->id()],
                        [
                            'user_id' => auth()->id(),
                            'cover_photo' => $cover_url,
                        ]
                    );
                }
            } else {
                $user->image = null;
                $user->save();
                $this->removeLink($user->id, get_class($user));

                if ($request->profile_image) {
                    $image = $this->mediaManagerRepository->store($request->profile_image);
                    $user->image = $this->generateLink($image->id, $user->id, get_class($user), 'image');
                    $user->save();
                }

                $userInfo = $user->userInfo()->updateOrCreate(
                    ['user_id' => auth()->id()],
                    [
                        'cover_photo' => null
                    ]
                );

                $this->removeLink($userInfo->id, get_class($userInfo));

                if ($request->cover_photo) {
                    $image = $this->mediaManagerRepository->store($request->cover_photo);
                    $userInfo->cover_photo = $this->generateLink($image->id, $user->id, get_class($user), 'cover_photo');
                    $userInfo->save();
                }
            }
        });
    }
    public function aboutUpdate(object $request): void
    {
        DB::transaction(function () use ($request) {
            $user = User::find(auth()->id());
            $user->update(['job_title' => $request->job_title, 'about' => $request->about]);

            $user->userInfo()->updateOrCreate(
                ['user_id' => auth()->id()],
                [
                    'user_id' => auth()->id(),
                    'short_description' => $request->short_description,
                ]
            );
        });
    }
    public function educationStore(object $request): void
    {
        UserEducation::create([
            'user_id' => auth()->id(),
            'institution' => $request->institution,
            'degree' => $request->degree,
            'start_date' => isset($request->start_date) ? date('Y-m-d', strtotime($request->start_date)) : null,
            'end_date' => isset($request->end_date) ? date('Y-m-d', strtotime($request->end_date)) : null,
        ]);
    }
    public function educationUpdate(object $request): void
    {
        UserEducation::where('id', $request->id)->update([
            'institution' => $request->institution,
            'degree' => $request->degree,
            'start_date' => isset($request->start_date) ? date('Y-m-d', strtotime($request->start_date)) : null,
            'end_date' => isset($request->end_date) ? date('Y-m-d', strtotime($request->end_date)) : null,
        ]);
    }
    public function educationDestroy(object $request): void
    {
        UserEducation::destroy($request->education_id);
    }
    public function experienceStore(object $request): void
    {
        UserExperience::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'company_name' => $request->company_name,
            'currently_working' => (bool) $request->is_currently_working,
            'start_date' => isset($request->start_date) ? Carbon::createFromFormat('m-d-Y', $request->start_date)->format('Y-m-d') : null,
            'end_date' => isset($request->end_date) ? Carbon::createFromFormat('m-d-Y', $request->end_date)->format('Y-m-d') : null,
        ]);
    }
    public function experienceUpdate(object $request): void
    {
        UserExperience::where('id', $request->experience_id)->update([
            'title' => $request->title,
            'company_name' => $request->company_name,
            'currently_working' => (bool)$request->is_currently_working,
            'start_date' => isset($request->start_date) ? Carbon::createFromFormat('d-m-Y', $request->start_date)->format('Y-m-d') : null,
            'end_date' => isset($request->end_date) ? Carbon::createFromFormat('d-m-Y', $request->end_date)->format('Y-m-d') : null,
        ]);
    }
    public function experienceDestroy(object $request): void
    {
        UserExperience::destroy($request->experience_id);
    }
    public function skillStore(object $request): void
    {
        UserSkill::updateOrCreate(
            [
                'user_id' => auth()->id(),
            ],
            [
                'user_id' => auth()->id(),
                'skills' => $request->skills,
            ]
        );
    }
    public function extraInfoUpdate(object $request): void
    {
        $request->merge([
            'country' => $request->country_id,
            'state' => $request->state_id,
            'city' => $request->city_id,
            'zip' => $request->zip_code,
        ]);

        User::where('id', auth()->id())->update([
            'gender' => $request->gender,
            'dob' => $request->dob ? Carbon::createFromFormat('d-m-Y', $request->dob)->format('Y-m-d') : null,
            'country' => $request->country,
            'state' => $request->state,
            'city' => $request->city,
            'zip' => $request->zip,
            'address' => $request->address,
        ]);
    }
    public function documentUpdate(object $request): void
    {
        $from = $request->get('from');
        $passport = UserDocument::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'name' => 'passport'
            ],
            [
                'document' => null,
            ]
        );
        if ($from == 'frontend') {
            if ($request->passport) {
                $image = $this->mediaManagerRepository->store($request->passport);
                $passport->document = $this->generateLink($image->id, $passport->id, get_class($passport), 'document');
                $passport->save();
            }
        } else {
            $this->removeLink($passport->id, get_class($passport));
            if ($request->passport) {
                $image = $this->mediaManagerRepository->store($request->passport);
                $passport->document = $this->generateLink($image->id, $passport->id, get_class($passport), 'document');
                $passport->save();
            }
        }

        $nid = UserDocument::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'name' => 'nid'
            ],
            [
                'document' => null,
            ]
        );
        if ($from == 'frontend') {
            if ($request->nid) {
                $image = $this->mediaManagerRepository->store($request->nid);
                $nid->document = $this->generateLink($image->id, $nid->id, get_class($nid), 'document');
                $nid->save();
            }
        } else {
            $this->removeLink($nid->id, get_class($nid));
            if ($request->nid) {
                $image = $this->mediaManagerRepository->store($request->nid);
                $nid->document = $this->generateLink($image->id, $nid->id, get_class($nid), 'document');
                $nid->save();
            }
        }

        $oldDocuments = UserDocument::where('user_id', auth()->id())
            ->where('name', '!=', 'passport')
            ->where('name', '!=', 'nid')
            ->get();
        if ($from != 'frontend') {
            foreach ($oldDocuments as $oldDocument) {
                $oldDocument->document = null;
                $oldDocument->save();
                $this->removeLink($oldDocument->id, get_class($oldDocument));
            }
        }

        if ($request->other_documents) {
            foreach ($request->other_documents as $key => $document) {
                if (isset($document['document_name']) && $document['document_name']) {
                    $userDoc = UserDocument::updateOrCreate(
                        [
                            'user_id' => auth()->id(),
                            'name' => $document['document_name']
                        ]
                    );
                    if ($from == 'frontend') {
                        if (isset($document['document_image']) && $document['document_image']) {
                            $image = $this->mediaManagerRepository->store($document->document_image);
                            $userDoc->document = $this->generateLink($image->id, $userDoc->id, get_class($userDoc), 'document');
                        }
                    } else {
                        if (isset($document['document_image'])) {
                            $image = $this->mediaManagerRepository->store($document['document_image']);
                            $userDoc->document = $this->generateLink($image->id, $userDoc->id, get_class($userDoc), 'document');
                        }
                    }
                    $userDoc->save();
                }
            }
        }

        if ($request->document_name) {
            foreach ($request->document_name as $key => $document) {
                $userDoc = UserDocument::updateOrCreate(
                    [
                        'user_id' => auth()->id(),
                        'name' => $document
                    ]
                );
                if ($from == 'frontend') {
                    if (isset($request->document_image[$key]) && $request->document_image[$key]) {
                        $image = $this->mediaManagerRepository->store($request->document_image[$key]);
                        $userDoc->document = $this->generateLink($image->id, $userDoc->id, get_class($userDoc), 'document');
                    }
                } else {
                    if (isset($request->document_image[$key])) {
                        $image = $this->mediaManagerRepository->store($request->document_image[$key]);
                        $userDoc->document = $this->generateLink($image->id, $userDoc->id, get_class($userDoc), 'document');
                    }
                }
                $userDoc->save();
            }
        }
    }
    public function socialInfoUpdate(object $request): void
    {
        DB::transaction(function () use ($request) {
            User::where('id', auth()->id())->update([
                'facebook' => $request->facebook,
                'twitter' => $request->twitter,
                'linkedin' => $request->linkedin,
                'instagram' => $request->instagram,
            ]);

            UserInstantMessage::where('user_id', auth()->id())->delete();
            if ($request->instant_messaging) {
                foreach ($request->instant_messaging as $msg) {
                    if ($msg['service'] && $msg['username']) {
                        UserInstantMessage::create([
                            'user_id' => auth()->id(),
                            'service' => $msg['service'],
                            'username' => $msg['username'],
                        ]);
                    }
                }
            }
        });
    }
}
