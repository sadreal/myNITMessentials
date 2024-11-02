<?php

namespace App\Http\Resources\api\v2\VirtualClass;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassListResource extends JsonResource
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
            'title' => (string)$this->title,
            'status' => (bool)$this->status,
            'category' => [
                'id' => (int)$this->category->id,
                'name' => (string)$this->category->name,
            ],
            'subcategory' => [
                'id' => (int)$this->subcategory->id,
                'name' => (string)$this->subcategory->name,
            ],
            'language' => [
                'id' => (int)$this->language->id,
                'name' => (string)$this->language->name,
            ],
        ];
    }
}
