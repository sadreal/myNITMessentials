<?php

namespace App\Repositories\Eloquents;

use App\Repositories\Interfaces\LanguageRepositoryInterface;
use App\User;
use Illuminate\Support\Facades\File;
use Modules\Localization\Entities\Language;

class LanguageRepository implements LanguageRepositoryInterface
{
    public function languages(): object
    {
        $data = Language::where('status', 1)->get()
            ->map(function ($language) {
                return [
                    'id' => (int)$language->id,
                    'code' => (string)$language->code,
                    'name' => (string)$language->name,
                    'native' => (string)$language->native,
                    'rtl' => (bool)$language->rtl
                ];
            });

        return $data;
    }
    public function setLanguage(object $request): string
    {
        $language = Language::where('status', 1)->where('code', strtolower($request->language_code))->first();
        if (auth('api')->check()) {
            $user = User::find(auth('api')->id());
            $user->language_id = $language->id;
            $user->language_code = $language->code;
            $user->language_name = $language->name;
            $user->language_rtl = $language->rtl;
            $user->save();
            $setLang['code'] = $user->language_code;
        } else {
            session(['locale'           => $language->code]);
            session(['language_name'    => $language->name]);
            session(['language_rtl'     => $language->rtl]);

            $setLang['code']    = session('locale');
        }
        app()->setLocale($setLang['code']);
        return app()->getLocale();
    }
    public function authLang(object $request): array
    {
        if (auth('api')->check()) {
            $user   = auth('api')->user();
            $code   = empty($request->code) ? $user->language_code ?? 'en' : $request->code;
            $rtl    = empty($request->rtl) ? $user->language_rtl ?? 0 : $request->rtl;
        } else {
            $code   = Settings('language_code') ?? 'en';
            $rtl    = Settings('language_rtl') ?? 0;
        }

        try {
            $path   = resource_path("lang/$code/api.php");
            $values = File::getRequire($path);
        } catch (\Exception $e) {
            $path   = resource_path("lang/en/api.php");
            $values = File::getRequire($path);
        }

        $language = [];
        foreach ($values as $key => $value) {
            $language[$key] = $value;
        }
        $lang = json_decode(json_encode($language), true);

        $data['rtl']    = (bool)$rtl;
        $data['code']   = (string)$code;
        $data['lang']   = (object)$lang;

        return $data;
    }
}
