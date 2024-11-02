<?php

namespace App\Repositories\Interfaces;

interface ChapterRepositoryInterface
{
    public function chapters(object $request): object;
    public function update(object $request): void;
    public function create(object $request): void;
    public function delete(object $request): void;
    public function contents(object $request): object;
    public function rearrangeContents(object $request): void;
}
