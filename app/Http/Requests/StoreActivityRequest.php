<?php

namespace App\Http\Requests;

use App\Enums\ActivityType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', new Enum(ActivityType::class)],
            'subject' => 'required|string|max:255',
            'body' => 'nullable|string',
            'occurred_at' => 'required|date',
            'contact_id' => 'nullable|exists:contacts,id',
            'deal_id' => 'nullable|exists:deals,id',
        ];
    }
}
