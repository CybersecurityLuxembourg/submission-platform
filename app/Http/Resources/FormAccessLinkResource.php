<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FormAccessLinkResource extends JsonResource
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
            'form_id' => $this->form_id,
            'token' => $this->token,
            'name' => $this->name,
            'expires_at' => $this->expires_at,
            'max_submissions' => $this->max_submissions,
            'submission_count' => $this->submission_count,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 