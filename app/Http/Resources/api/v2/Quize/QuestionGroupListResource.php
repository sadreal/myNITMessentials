<?php

namespace App\Http\Resources\api\v2\Quize;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionGroupListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int)$this->id,
            'group_name' => (string)$this->title,
            'code' => (string)$this->code,
            'parent' => [
                'id' => (int)$this->parent->id,
                'name' => (string)$this->parent->title,
            ],
        ];
    }
}
