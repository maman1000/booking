<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    /**
     * Endpoint booking hanya untuk user terautentikasi (middleware auth:sanctum).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'service_schedule_id' => ['required', 'exists:service_schedules,id'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}
