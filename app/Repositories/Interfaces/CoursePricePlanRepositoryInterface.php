<?php

namespace App\Repositories\Interfaces;

interface CoursePricePlanRepositoryInterface
{
    public function storePlan(object $request): void;
    public function updatePlan(object $request): void;
    public function deletePlan(object $request): void;
}
