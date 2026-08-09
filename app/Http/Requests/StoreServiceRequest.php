<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Otorisasi sudah di middleware role:admin
    }

    public function rules(): array
    {
        // Untuk update (PUT/PATCH), semua field menjadi opsional
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        $required = $isUpdate ? 'sometimes|' : '';

        return [
            'name' => [$required . 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price_per_hour' => [$required . 'required', 'numeric', 'min:0'],
            'image' => ['nullable', 'string', 'max:255'],
            'status' => [$required . 'required', 'in:available,maintenance'],
        ];
    }
}
