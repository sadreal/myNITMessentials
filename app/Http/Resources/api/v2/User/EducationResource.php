<?php

namespace App\Http\Resources\api\v2\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EducationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => (int)$this->id,
            'degree'        => (string)$this->degree,
            'institution'   => (string)$this->institution,
            'start_date'    => (string)$this->start_date,
            'end_date'      => (string)$this->end_date
        ];
    }
}
