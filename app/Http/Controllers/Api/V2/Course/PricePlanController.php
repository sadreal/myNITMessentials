<?php

namespace App\Http\Controllers\Api\V2\Course;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\CoursePricePlanRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PricePlanController extends Controller
{
    private $pricePlanRepository;
    public function __construct(CoursePricePlanRepositoryInterface $pricePlanRepository)
    {
        $this->pricePlanRepository = $pricePlanRepository;
    }

    public function storePlan(Request $request): object
    {
        $rules = [
            'course_id' => 'required|exists:courses,id',
            'start_date' => 'required|date|date_format:m-d-Y',
            'end_date' => 'required|date|date_format:m-d-Y|after_or_equal:start_date',
            'title' => 'required|string',
            'discount' => 'required|numeric',
            'capacity' => 'required|numeric',
        ];
        $request->validate($rules, validationMessage($rules));

        $this->pricePlanRepository->storePlan($request);

        $response = [
            'success'   => true,
            'message'   => trans('api.Price plan added successfully'),
        ];
        return response()->json($response);
    }
    public function updatePlan(Request $request): object
    {
        $rules = [
            'course_id'     => 'required|exists:courses,id',
            'price_plan_id' => ['required', Rule::exists('price_plans', 'id')->where('price_planable_id', $request->course_id)],
            'start_date'    => 'required|date_format:m-d-Y',
            'end_date'      => 'required|date_format:m-d-Y|after_or_equal:start_date',
            'title'         => 'required|string',
            'discount'      => 'required|numeric',
            'capacity'      => 'required|numeric',
        ];
        $request->validate($rules, validationMessage($rules));

        $this->pricePlanRepository->updatePlan($request);

        $response = [
            'success'   => true,
            'message'   => trans('api.Price plan updated successfully'),
        ];
        return response()->json($response);
    }
    public function deletePlan(Request $request): object
    {
        $rules = [
            'course_id' => 'required|exists:courses,id',
            'price_plan_id' => ['required', Rule::exists('price_plans', 'id')->where('price_planable_id', $request->course_id)],
        ];
        $request->validate($rules, validationMessage($rules));

        $this->pricePlanRepository->deletePlan($request);

        return response()->json([
            'success'   => true,
            'message'   => trans('api.Price plan deleted successfully'),
        ]);
    }
}
