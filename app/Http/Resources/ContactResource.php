<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'firstName' => $this->first_name,
            'lastName' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'title' => $this->title,
            'companyName' => $this->company?->name,
            'companyId' => $this->company_id,
            'lastContactedAt' => $this->last_contacted_at?->toIso8601String(),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'createdAt' => $this->created_at?->toIso8601String(),
        ];
    }
}
