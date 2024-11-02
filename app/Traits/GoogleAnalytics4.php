<?php

namespace App\Traits;


use Exception;
use Freshbitsweb\LaravelGoogleAnalytics4MeasurementProtocol\Facades\GA4;


trait GoogleAnalytics4
{
    public function postEvent($data)
    {
        try {
            if (session()->get(config('google-analytics-4-measurement-protocol.client_id_session_key'))) {
                return GA4::postEvent($data);
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
