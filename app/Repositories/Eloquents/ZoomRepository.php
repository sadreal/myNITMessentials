<?php

namespace App\Repositories\Eloquents;

use App\Repositories\Interfaces\ZoomRepositoryInterface;
use Modules\Zoom\Entities\ZoomSetting;

class ZoomRepository implements ZoomRepositoryInterface
{
    public function configure(object $request): object
    {
        $rules = [
            'zoom_account_id'       => 'required',
            'zoom_client_id'        => 'required',
            'zoom_client_secret'    => 'required',
        ];

        $request->validate($rules, validationMessage($rules));

        ZoomSetting::updateOrCreate([
            'user_id' => auth()->id() ?? 1,
        ], [
            'user_id'               => auth()->id() ?? 1,
            'zoom_account_id'       => $request['zoom_account_id'],
            'zoom_client_id'        => $request['zoom_client_id'],
            'zoom_client_secret'    => $request['zoom_client_secret'],
        ]);

        $response = [
            'success'   => true,
            'message'   => trans('api.Zoom configured successfully')
        ];

        return response()->json($response);
    }

    public function settings(object $request): object
    {
        ZoomSetting::updateOrCreate([
            'user_id'   => auth()->id() ?? 1,
        ], [
            'user_id'               => auth()->id() ?? 1,
            'package_id'            => $request['package_id'] ?? 1,
            'host_video'            => $request['host_video'] ?? 0,
            'participant_video'     => $request['participant_video'] ?? 0,
            'join_before_host'      => $request['join_before_host'] ?? 0,
            'audio'                 => $request['audio'] ?? 'both',
            'auto_recording'        => $request['auto_recording'] ?? 'none',
            'approval_type'         => $request['approval_type'] ?? 0,
            'mute_upon_entry'       => $request['mute_upon_entry'] ?? 0,
            'waiting_room'          => $request['waiting_room'] ?? 0,
        ]);

        $response = [
            'success'   => true,
            'message'   => trans('api.Zoom configured successfully')
        ];

        return response()->json($response);
    }

    public function getConfigSetting(): object
    {
        $data = ZoomSetting::where('user_id', auth()->id() ?? 1)
            ->select('approval_type as class_join_approval', 'auto_recording', 'audio as audio_option', 'package_id as package', 'zoom_account_id', 'zoom_client_id', 'zoom_client_secret', 'host_video', 'participant_video', 'join_before_host', 'waiting_room', 'mute_upon_entry')
            ->firstOrFail();

        $response = [
            'success'   => true,
            'data'      => $data,
            'message'   => trans('api.Getting zoom settings'),
        ];

        return response()->json($response);
    }

    public function approvelTypes(): object
    {
        $types = [
            [
                'id'    => 0,
                'name'  => 'Automatically'
            ],
            [
                'id'    => 1,
                'name'  => 'Manually Approve'
            ],
            [
                'id'    => 2,
                'name'  => 'No Registration Required'
            ],
        ];

        $response = [
            'success'   => true,
            'data'      => $types,
            'message'   => trans('api.Getting zoom approval type list')
        ];

        return response()->json($response);
    }

    public function autoRecordings(): object
    {
        $list = [
            [
                'id' => 'none',
                'name' => 'None',
            ],
            [
                'id' => 'local',
                'name' => 'Local',
            ],
            [
                'id' => 'cloud',
                'name' => 'Cloud',
            ],
        ];

        $response = [
            'success'   => true,
            'data'      => $list,
            'message'   => trans('api.Getting zoom auto recording list')
        ];

        return response()->json($response);
    }

    public function audios(): object
    {
        $list = [
            [
                'id' => 'both',
                'name' => 'Both',
            ],
            [
                'id' => 'telephony',
                'name' => 'Telephony',
            ],
            [
                'id' => 'voip',
                'name' => 'Voip',
            ],
        ];

        $response = [
            'success'   => true,
            'data'      => $list,
            'message'   => trans('api.Getting zoom audio option list')
        ];

        return response()->json($response);
    }

    public function packages(): object
    {
        $list = [
            [
                'id' => 1,
                'name' => 'Basic (Free)'
            ],
            [
                'id' => 2,
                'name' => 'Pro'
            ],
            [
                'id' => 3,
                'name' => 'Business'
            ],
            [
                'id' => 4,
                'name' => 'Enterprise'
            ],
        ];

        $response = [
            'success'   => true,
            'data'      => $list,
            'message'   => trans('api.Getting zoom package list')
        ];

        return response()->json($response);
    }
}
