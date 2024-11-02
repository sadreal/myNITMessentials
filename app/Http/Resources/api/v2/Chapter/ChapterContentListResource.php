<?php

namespace App\Http\Resources\api\v2\Chapter;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChapterContentListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if ((bool)$this->is_quiz) {
            $type = 'quiz';
        } elseif ((bool)$this->is_assignment) {
            $type = 'assignment';
        } else {
            $type = 'lesson';
        }

        return [
            'id' => (int)$this->id,
            'course_id' => (int)$this->course_id,
            'chapter_id' => (int)$this->chapter_id,
            'type' => (string)ucwords($type),
            'title' => (string)$this->name,
            'lock' => (bool)$this->is_lock,
        ];
    }
}
