<?php

namespace App\Http\Resources\api\v2\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthUserDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $permissions = [];
        foreach ($this->role->permissions as $permission) {
            $permissions[] = [
                'name' => (string)$permission->name,
                'route' => (string)$permission->route,
            ];
        }

        return [
            'id' => (int)$this->id,
            'name' => (string)$this->name,
            'photo' => $this->photo ? (string)asset($this->photo) : '',
            'image' => $this->image ? (string)asset($this->image) : '',
            'avatar' => $this->avatar ? (string)asset($this->avatar) : '',
            'email' => (string)$this->email,
            'role' => [
                'id' => (int)$this->role->id,
                'name' => (string)$this->role->name,
                'type' => (string)$this->role->type,
            ],
            'permissions' => $permissions,
        ];
    }
}
