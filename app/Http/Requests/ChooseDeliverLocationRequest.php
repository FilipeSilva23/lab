<?php

namespace App\Http\Requests;


use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
class ChooseDeliverLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role === UserRole::Student;
    }

    public function rules(): array
    {
        return [
            'deliver_location_id' => ['required', 'integer', 'exists:deliver_locations,id'],
        ];
    }
}
