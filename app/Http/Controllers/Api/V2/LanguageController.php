<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LanguageController extends Controller
{
    private $language;

    public function __construct(LanguageRepositoryInterface $language)
    {
        $this->language = $language;
    }

    public function languages(): object
    {
        return response()->json([
            'success'   => true,
            'data'      => $this->language->languages(),
            'message'   => trans('api.Getting Language list'),
        ]);
    }

    public function setLanguage(Request $request): object
    {
        $rules = [
            'language_code' => ['required', Rule::exists('languages', 'code')->where('status', 1)]
        ];
        $request->validate($rules, validationMessage($rules));

        return response()->json([
            'success'   => true,
            'data'      => $this->language->setLanguage($request),
            'message'   => trans('api.Successfully set lang'),
        ]);
    }

    public function getLang(Request $request): object
    {
        return response()->json([
            'success'   => true,
            'data'      => $this->language->authLang($request),
            'message'   => trans('api.Getting data'),
        ]);
    }
}
