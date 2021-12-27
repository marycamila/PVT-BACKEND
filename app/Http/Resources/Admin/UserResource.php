<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Controllers\Admin\UserController;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'username' => $this->username,
            'position' => $this->position,
            'phone' => $this->phone,
            'is_commission' => $this->is_commission,
            'active' => $this->active,
            'status' => $this->status,
            'city_id'=>$this->city_id,
            'created_at' => $this->created_at,
            'deleted_at' => $this->deleted_at,
            'modules' => UserController::user_module_role_permision($this->id)? UserController::user_module_role_permision($this->id):null
        ];
    }
}
