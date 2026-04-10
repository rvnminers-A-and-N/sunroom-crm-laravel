<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DealResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'value' => (float) $this->value,
            'stage' => $this->stage->value,
            'contactId' => $this->contact_id,
            'contactName' => $this->contact ? trim("{$this->contact->first_name} {$this->contact->last_name}") : '',
            'companyId' => $this->company_id,
            'companyName' => $this->company?->name,
            'expectedCloseDate' => $this->expected_close_date?->toIso8601String(),
            'closedAt' => $this->closed_at?->toIso8601String(),
            'createdAt' => $this->created_at?->toIso8601String(),
        ];
    }
}
