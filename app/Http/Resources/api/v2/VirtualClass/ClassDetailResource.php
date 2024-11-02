<?php

namespace App\Http\Resources\api\v2\VirtualClass;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $assistantInstructors = [];
        if ($this->course->assistantInstructorsIds) {
            foreach ($this->course->assistantInstructorsIds as $id) {
                $assistantInstructors[] = User::select('id', 'name')->find($id)->makeHidden('blocked_by_me');
            }
        }
        $hostData = null;

        switch ($this->host) {
            case 'Zoom':
                $host = 'zoom';
                foreach ($this->zoomMeetings as $zoom) {
                    $hostData = [
                        'zoom_password' => (string)$zoom->password
                    ];
                }
                break;

            case 'BBB':
                $host = 'bbb';
                foreach ($this->bbbMeetings as $bbb) {
                    $hostData = [
                        'attendee_password' => (string)$bbb->attendee_password,
                        'moderator_password' => (string)$bbb->moderator_password,
                    ];
                }
                break;

            case 'Jitsi':
                $host = 'jitsi';
                foreach ($this->jitsiMeetings as $jitsi) {
                    $hostData = [
                        'jitsi_meeting_id' => (string)$jitsi->meeting_id,
                    ];
                }
                break;

            case 'InAppLiveClass':
                $host = 'in-app-live';
                $hostSetting = json_decode($this->host_setting);
                foreach ($this->inAppMeetings as $app) {
                    $hostData = [
                        'chat' => (bool)$hostSetting->chat,
                    ];
                }
                break;

            default:
                $host = 'zoom';
                foreach ($this->zoomMeetings as $zoom) {
                    $hostData = [
                        'zoom_password' => (string)$zoom->password
                    ];
                }
                break;
        }

        return [
            'class_id' => (int)$this->id,
            'title' => (string)$this->title,
            'description' => (string)$this->course->about,
            'assign_instructor' => [
                'id' => (int)$this->course->instructor->id,
                'name' => (string)$this->course->instructor->name,
            ],
            'assistant_instructor' => $assistantInstructors,
            'category' => [
                'id' => (int)$this->category->id,
                'name' => (string)$this->category->name,
            ],
            'subcategory' => [
                'id' => (int)$this->subCategory->id,
                'name' => (string)$this->subCategory->name,
            ],
            'language' => [
                'id' => (int)$this->language->id,
                'name' => (string)$this->language->name,
            ],
            'is_free_class' => $this->fees < 1,
            'fee' => (float)$this->fees,
            'image' => $this->image ? (string)asset($this->image) : '',
            'view_scope' => (int)$this->course->scope,
            'type' => $this->type ? 'Continuous class' : 'Single class',
            'start_date' => (string)$this->start_date,
            'end_date' => (string)$this->end_date,
            'time' => (string)date('h:i A', strtotime($this->time)),
            'host' => (string)ucwords($host),
            $host => $hostData,
            'certificate' => [
                'id' => (int)$this->course->certificate->id,
                'name' => (string)$this->course->certificate->title,
            ],
            'capacity' => (int)$this->capacity,
            'support' => (bool)$this->course->support,
        ];
    }
}
