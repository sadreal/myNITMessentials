<?php

namespace App\Http\Resources\api\v2\VirtualClass;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassScheduleListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $hostData = [];
        switch ($this->host) {
            case 'Zoom':
                foreach ($this->zoomMeetings as $zoom) {
                    $hostData[] = [
                        'id' => (int)$zoom->id,
                        'topic' => (string)$zoom->topic,
                        'date' => (string)$zoom->date_of_meeting,
                        'time' => (string)date('h:i A', strtotime($zoom->time_of_meeting)),
                        'duration' => (int)$zoom->meeting_duration,
                        'datetime' => (string)strtotime($zoom->date_of_meeting . $zoom->time_of_meeting),
                        'host' => (string)$this->host,
                        'zoom' => [
                            'meeting_id' => $zoom->meeting_id
                        ],
                    ];
                }
                break;

            case 'BBB':
                foreach ($this->bbbMeetings as $bbb) {
                    $hostData[] = [
                        'id' => (int)$bbb->id,
                        'topic' => (string)$bbb->topic,
                        'date' => (string)$bbb->date,
                        'time' => (string)date('h:i A', strtotime($bbb->time)),
                        'duration' => (int)$bbb->duration,
                        'datetime' => (string)$bbb->datetime,
                        'host' => (string)$this->host,
                        'bbb' => [
                            'attendee_password' => $bbb->attendee_password,
                            'moderator_password' => $bbb->moderator_password,
                        ],
                    ];
                }
                break;

            case 'Jitsi':
                foreach ($this->jitsiMeetings as $jitsi) {
                    $hostData[] = [
                        'id' => (int)$jitsi->id,
                        'topic' => (string)$jitsi->topic,
                        'date' => (string)$jitsi->date,
                        'time' => (string)date('h:i A', strtotime($jitsi->time)),
                        'duration' => (int)$jitsi->duration,
                        'datetime' => (string)$jitsi->datetime,
                        'host' => (string)$this->host,
                        'jitsi' => [
                            'meeting_id' => (string)$jitsi->meeting_id
                        ],
                    ];
                }
                break;

            case 'InAppLiveClass':
                $hostSetting = json_decode($this->host_setting);
                foreach ($this->inAppMeetings as $app) {
                    $hostData[] = [
                        'id'        => (int)$app->id,
                        'topic'     => (string)$app->topic,
                        'date'      => (string)$app->date,
                        'time'      => (string)date('h:i A', strtotime($app->time)),
                        'duration'  => (int)$app->duration,
                        'datetime'  => (string)$app->datetime,
                        'host'      => (string)$this->host,
                        'jitsi'     => [
                            'chat' => (bool)$hostSetting->chat
                        ],
                    ];
                }
                break;

            default:
                foreach ($this->customMeetings as $custom) {
                    $hostData[] = [
                        'id'        => (int)$custom->id,
                        'topic'     => (string)$custom->topic,
                        'date'      => (string)$custom->date,
                        'time'      => (string)date('h:i A', strtotime($custom->time)),
                        'duration'  => (int)$custom->duration,
                        'datetime'  => (string)$custom->datetime,
                        'host'      => (string)$this->host,
                    ];
                }
                break;
        }

        return $hostData;
    }
}
