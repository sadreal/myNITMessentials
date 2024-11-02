<?php

namespace App\Repositories\Interfaces;

interface CourseRepositoryInterface
{
    public function courses(object $request): object;
    public function changeStatus(object $request): void;
    public function courseDetail(object $request): object;
    public function courseUpdate(object $request): bool;
    public function categories(object $request): object;
    public function subcategories(object $request): object;
    public function storeCourse(object $request): void;
    public function levels(object $request): object;
    public function chapterRearrange(object $request): void;
    public function certificateList(): object;
    public function assignCertificate(object $request): object;
    public function categoryDetail(object $request): object;
    public function categoryStore(object $request): void;
    public function changeCategoryStatus(object $request): void;
    public function courseCategoryDelete(object $request): void;
    public function storeCourseLevel(object $request): void;
    public function updateCourseLevel(object $request): void;
    public function changeCourseLevelStatus(object $request): void;
    public function deleteCourseLevel(object $request): object;
    public function courseDelete(object $request): void;
    public function categoryUpdate(object $request): void;
}
