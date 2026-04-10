<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'subject' => $this->subject,
            'body' => $this->body,
            'aiSummary' => $this->ai_summary,
            'contactId' => $this->contact_id,
            'contactName' => $this->contact ? trim("{$this->contact->first_name} {$this->contact->last_name}") : null,
            'dealId' => $this->deal_id,
            'dealTitle' => $this->deal?->title,
            'userName' => $this->user?->name ?? '',
            'occurredAt' => $this->occurred_at?->toIso8601String(),
            'createdAt' => $this->created_at?->toIso8601String(),
        ];
    }
}
