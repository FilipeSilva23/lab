<?php

namespace App\Http\Requests;

use App\Models\DeliverLocation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeliverLocationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('manage', DeliverLocation::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = $this->route('deliver_location')?->id;

        return [
            'name_pt' => ['required', 'string', 'max:255', Rule::unique('deliver_locations', 'name_pt')->ignore($id)],
            'name_en' => ['required', 'string', 'max:255', Rule::unique('deliver_locations', 'name_en')->ignore($id)],
        ];
    }
}
