<?php

namespace App\Http\Requests;

use App\Enums\DealStage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreDealRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'value' => 'required|numeric|min:0',
            'stage' => ['required', new Enum(DealStage::class)],
            'contact_id' => 'required|exists:contacts,id',
            'company_id' => 'nullable|exists:companies,id',
            'expected_close_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ];
    }
}
