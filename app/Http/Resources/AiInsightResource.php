<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AiInsightResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'insight' => $this->insight,
            'generatedAt' => $this->generated_at?->toIso8601String(),
        ];
    }
}
