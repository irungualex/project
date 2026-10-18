<?php

namespace App\Http\Requests;

use App\Models\ServiceRequest as CivicServiceRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterServiceRequests extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(CivicServiceRequest::STATUSES)],
            'category' => ['nullable', Rule::in(CivicServiceRequest::CATEGORIES)],
        ];
    }
}
