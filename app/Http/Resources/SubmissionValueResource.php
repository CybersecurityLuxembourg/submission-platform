<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubmissionValueResource extends JsonResource
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
            'submission_id' => $this->submission_id,
            'form_field_id' => $this->form_field_id,
            'value' => $this->value,
            'field' => new FormFieldResource($this->whenLoaded('field')),
        ];
    }
} 