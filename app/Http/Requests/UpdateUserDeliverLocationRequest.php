<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Models\DeliverLocation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserDeliverLocationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // only admins (same policy as managing locations), and never to change another admin
        return $this->user()->can('manage', DeliverLocation::class)
            && $this->route('user')->role !== UserRole::Admin;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'deliver_location_id' => ['required', 'integer', 'exists:deliver_locations,id'],
        ];
    }
}
