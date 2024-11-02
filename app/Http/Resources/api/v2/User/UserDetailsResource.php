<?php

namespace App\Http\Resources\api\v2\User;

use App\Models\UserDocument;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserDetailsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $nid = UserDocument::where('user_id', $this->id)->where('name', 'nid')->first();
        $passport = UserDocument::where('user_id', $this->id)->where('name', 'passport')->first();
        $otherDocuments = UserDocument::where('user_id', $this->id)->whereNotIn('name', ['nid', 'passport'])->pluck('document', 'name');

        return [
            'id'            => (int)$this->id,
            'basic_info'    => [
                'image'     => file_exists($this->avatar) ? (string)asset($this->avatar) : (string)null,
                'name'      => (string)$this->name,
                'email'     => (string)$this->email,
                'phone'     => (string)$this->phone,
                'currency'  => (string)trim($this->currency->code),
                'language'  => (string)trim($this->userLanguage->name),
                'timezone'  => (string)trim($this->userInfo->timezone->time_zone)
            ],
            'about'         => [
                'job_title' => (string)$this->job_title,
                'short_description' => (string)trim($this->userInfo->short_description),
                'biography' => (string)$this->about,
            ],
            'education'    => EducationResource::collection($this->userEducations),
            'experience'   => ExperienceResource::collection($this->userExperiences),
            'skills'        => [
                'name'      => (string)@$this->userSkill->skills
            ],
            'finantial' => (string)@$this->userPayoutAccount->payoutAccount->title,
            'api'   => [
                'zoom_api_key'          => (string)$this->zoom_api_key_of_user,
                'zoom_api_serect_key'   => (string)$this->zoom_api_serect_of_user
            ],
            'extra_information' => [
                'gender' => (string)$this->gender,
                'date_of_birth' => (string)$this->dob,
                'country' => (string)$this->userCountry->name,
                'state' => (string)$this->stateDetails->name,
                'city' => (string)$this->cityDetails->name,
                'zip_code' => (string)$this->zip,
                'address' => (string)$this->address,
            ],
            'identity_and_documents' => [
                'nid'       => $nid?->document,
                'passport'  => $passport?->document,
                'other_documents' => $otherDocuments,
            ],
            'social_and_contact'  => [
                'facebook'  => (string)$this->facebook,
                'twitter'   => (string)$this->twitter,
                'linkedin'  => (string)$this->linkedin,
                'instagram'   => (string)$this->instagram,
                'others' => $this->otherSocialInfo->pluck('username', 'service'),
            ],
        ];
    }
}
