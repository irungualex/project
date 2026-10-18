<?php

namespace App\Http\Requests;

use App\Models\ServiceRequest as CivicServiceRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdvanceServiceRequestStatus extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(CivicServiceRequest::STATUSES)],
            'note' => ['nullable', 'string', 'max:240'],
        ];
    }
}
