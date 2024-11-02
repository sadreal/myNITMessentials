<?php

namespace App\Http\Controllers\Api\V2\GeneralSetting;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\GeneralSettingsInterface;
use Illuminate\Http\Request;

class GeneralSettingController extends Controller
{
    private $generalSetting;

    public function __construct(GeneralSettingsInterface $generalSetting)
    {
        $this->generalSetting = $generalSetting;
    }
    public function default(): object
    {
        return $this->generalSetting->defaultSettings();
    }
    public function currencies(Request $request): object
    {
        return $this->generalSetting->currencies($request);
    }
    public function timezones(Request $request): object
    {
        return $this->generalSetting->timezones($request);
    }
    public function countries(Request $request): object
    {
        return $this->generalSetting->countries($request);
    }
    public function states(Request $request): object
    {
        return $this->generalSetting->states($request);
    }
    public function cities(Request $request): object
    {
        return $this->generalSetting->cities($request);
    }
}
