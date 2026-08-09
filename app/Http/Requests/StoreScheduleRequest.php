<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        $required = $isUpdate ? 'sometimes|' : '';

        return [
            'service_id' => [$required . 'required', 'exists:services,id'],
            'day_of_week' => [$required . 'required', 'integer', 'min:0', 'max:6'],
            'start_time' => [$required . 'required', 'date_format:H:i'],
            'end_time' => [$required . 'required', 'date_format:H:i', 'after:start_time'],
            'is_active' => ['boolean'],
        ];
    }
}
