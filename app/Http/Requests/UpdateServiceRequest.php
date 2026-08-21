<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Admin only (middleware sudah menangani)
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price_per_hour' => 'sometimes|required|numeric|min:0',
            'status' => 'sometimes|required|in:available,maintenance',
        ];
    }
}
