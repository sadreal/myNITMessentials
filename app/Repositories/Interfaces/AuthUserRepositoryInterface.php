<?php

namespace App\Repositories\Interfaces;

interface AuthUserRepositoryInterface
{
    public function basicInfoUpdate(object $request): void;
    public function aboutUpdate(object $request): void;
    public function educationStore(object $request): void;
    public function educationUpdate(object $request): void;
    public function educationDestroy(object $request): void;
    public function experienceStore(object $request): void;
    public function experienceUpdate(object $request): void;
    public function experienceDestroy(object $request): void;
    public function skillStore(object $request): void;
    public function extraInfoUpdate(object $request): void;
    public function documentUpdate(object $request): void;
    public function socialInfoUpdate(object $request): void;
}
