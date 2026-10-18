<?php

namespace App\Http\Requests;

use App\Models\ServiceRequest as CivicServiceRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && ! $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'category' => ['required', Rule::in(CivicServiceRequest::CATEGORIES)],
            'location' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string', 'min:10', 'max:1200'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1536'],
        ];
    }
}
