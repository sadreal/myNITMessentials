<?php

namespace App\Http\Resources\api\v2\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExperienceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => (int)$this->id,
            'title'             => (string)$this->title,
            'company_name'      => (string)$this->company_name,
            'currently_working' => (bool)$this->currently_working,
            'start_date'        => (string)$this->start_date,
            'end_date'          => (string)$this->end_date
        ];
    }
}
