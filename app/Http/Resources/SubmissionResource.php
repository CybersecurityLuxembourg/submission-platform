<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubmissionResource extends JsonResource
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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'ip_address' => $this->ip_address,
            'status' => $this->status,
            'values' => SubmissionValueResource::collection($this->whenLoaded('values')),
            'form' => new FormResource($this->whenLoaded('form')),
        ];
    }
} 