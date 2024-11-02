<?php

namespace App\Repositories\Eloquents;

use App\Country;
use App\Http\Resources\api\v2\GeneralSettings\CityListResource;
use App\Http\Resources\api\v2\GeneralSettings\CountryListResource;
use App\Http\Resources\api\v2\GeneralSettings\CurrencyListResource;
use App\Http\Resources\api\v2\GeneralSettings\DefaultSettingsResource;
use App\Http\Resources\api\v2\GeneralSettings\StateListResource;
use App\Http\Resources\api\v2\GeneralSettings\TimezoneListResource;
use App\Models\SpnCity;
use App\Repositories\Interfaces\GeneralSettingsInterface;
use App\State;
use Modules\Setting\Model\GeneralSetting;
use Modules\Setting\Model\TimeZone;
use Modules\SystemSetting\Entities\Currency;

class GeneralSettingsRepository implements GeneralSettingsInterface
{
    public function defaultSettings(): object
    {
        $setting    = GeneralSetting::pluck('value', 'key')->only('site_title', 'student_reg', 'instructor_reg', 'currency_code', 'currency_symbol');
        $data       = new DefaultSettingsResource($setting);

        $response = [
            'success'   => true,
            'data'      => $data,
            'message'   => trans('api.Get settings successful'),
        ];

        return response()->json($response);
    }
    public function currencies(object $request): object
    {
        $data = Currency::whereStatus('1')->when($search = $request->search, function ($q) use ($search) {
            $q->whereLike('name', $search);
        })->paginate($request->per_page ?? 10);

        $response = [
            'success' => true,
            'data' => CurrencyListResource::collection($data),
            'message' => trans('api.Get currency list successfully'),
        ];
        return response()->json($response, 200);
    }
    public function timezones(object $request): object
    {
        $data = TimeZone::when($search = $request->search, function ($q) use ($search) {
            $q->whereLike('code', $search);
        })->paginate($request->per_page ?? 10);

        $response = [
            'success' => true,
            'data' => TimezoneListResource::collection($data),
            'message' => trans('api.Get timezone list successfully'),
        ];

        return response()->json($response, 200);
    }
    public function countries(object $request): object
    {
        $countries = Country::where('active_status', 1)->when($search = $request->search, function ($countries) use ($search) {
            $countries->whereLike('name', $search);
        })->paginate($request->per_page ?? 10);
        $response = [
            'success' => true,
            'data' => CountryListResource::collection($countries),
            'message' => trans('api.Get country list successfully'),
        ];
        return response()->json($response);
    }
    public function states(object $request): object
    {
        $rules = [
            'country_id' => 'required|exists:states,country_id'
        ];
        $request->validate($rules, validationMessage($rules));

        $state = State::where('country_id', $request->country_id)->when($search = $request->search, function ($states) use ($search) {
            $states->whereLike('name', $search);
        })->paginate($request->per_page ?? 10);

        $response = [
            'success' => true,
            'data' => StateListResource::collection($state),
            'message' => trans('api.Get state list successfully'),
        ];

        return response()->json($response);
    }
    public function cities(object $request): object
    {
        $rules = [
            'state_id' => 'required|exists:spn_cities,state_id',
        ];

        $request->validate($rules, validationMessage($rules));

        $cities = SpnCity::where('state_id', $request->state_id)->when($search = $request->search, function ($cities) use ($search) {
            $cities->whereLike('name', $search);
        })->select('id', 'name')->paginate($request->per_page ?? 10);

        $response = [
            'success' => true,
            'data' => CityListResource::collection($cities),
            'message' => trans('api.Get city list successfully'),
        ];

        return response()->json($response);
    }
}
