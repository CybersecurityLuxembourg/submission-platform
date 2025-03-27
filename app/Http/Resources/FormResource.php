<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FormResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'visibility' => $this->visibility,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'categories' => FormCategoryResource::collection($this->whenLoaded('categories')),
            'fields' => FormFieldResource::collection($this->whenLoaded('fields')),
            'access_links' => FormAccessLinkResource::collection($this->whenLoaded('accessLinks')),
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
} 