<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FormFieldResource extends JsonResource
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
            'form_category_id' => $this->form_category_id,
            'type' => $this->type,
            'label' => $this->label,
            'placeholder' => $this->placeholder,
            'required' => (bool) $this->required,
            'options' => $this->options,
            'validation_rules' => $this->validation_rules,
            'order' => $this->order,
        ];
    }
} 