<?php

namespace App\Repositories\Eloquents;

use App\Repositories\Interfaces\CoursePricePlanRepositoryInterface;
use Carbon\Carbon;
use Modules\CourseSetting\Entities\Course;
use Modules\EarlyBird\Entities\PricePlan;

class CoursePricePlanRepository implements CoursePricePlanRepositoryInterface
{
    public function storePlan(object $request): void
    {
        $request->merge([
            'price_planable_id' => $request->course_id,
        ]);

        session()->flash('type', 'earlyBirdPrice');

        $startDate = Carbon::createFromFormat('m-d-Y', $request->start_date);
        $endDate = Carbon::createFromFormat('m-d-Y', $request->end_date);

        $price_plan = new PricePlan();
        $price_plan->title = $request->title;
        $price_plan->discount_amount = $request->discount;
        $price_plan->capacity = $request->capacity;
        $price_plan->start_date = $startDate->format('Y-m-d');
        $price_plan->end_date = $endDate->format('Y-m-d');
        $price_plan->status = 1;
        $price_plan->price_planable_id = $request->price_planable_id;
        $price_plan->price_planable_type = Course::class;
        $price_plan->save();
    }

    public function updatePlan(object $request): void
    {
        $request->merge([
            'price_planable_id' => $request->course_id,
        ]);

        session()->flash('type', 'earlyBirdPrice');

        $startDate = Carbon::createFromFormat('m-d-Y', $request->start_date);
        $endDate = Carbon::createFromFormat('m-d-Y', $request->end_date);

        $price_plan = PricePlan::where('price_planable_id', $request->course_id)->find($request->price_plan_id);
        $price_plan->title = $request->title;
        $price_plan->discount_amount = $request->discount;
        $price_plan->capacity = $request->capacity;
        $price_plan->start_date = $startDate->format('Y-m-d');
        $price_plan->end_date = $endDate->format('Y-m-d');
        $price_plan->status = 1;
        $price_plan->save();
    }

    public function deletePlan(object $request): void
    {
        session()->flash('type', 'earlyBirdPrice');
        $plan = PricePlan::where('price_planable_id', $request->course_id)->findOrFail($request->price_plan_id);
        $plan->delete();
    }
}
