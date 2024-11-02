<?php

namespace App\Http\Resources\api\v2\Quize;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        switch ($this->type) {
            case 'M':
                $typeName = trans('quiz.Multiple Choice');
                break;
            case 'S':
                $typeName = trans('quiz.Short Answer');
                break;
            case 'L':
                $typeName = trans('quiz.Long Answer');
                break;
            case 'X':
                $typeName = trans('quiz.Matching Type');
                break;

            default:
                $typeName = trans('quiz.Fill In The Blanks');
                break;
        }
        return [
            'id' => (int)$this->id,
            'type_name' => (string)$typeName,
            'type' => (string)$this->type,
            'question' => (string)$this->question,
        ];
    }
}
