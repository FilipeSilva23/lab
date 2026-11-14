<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('manage', Category::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name_pt' => ['required', 'string', 'max:255', Rule::unique('categories', 'name_pt')->ignore($this->route('category'))],
            'name_en' => ['required', 'string', 'max:255', Rule::unique('categories', 'name_en')->ignore($this->route('category'))],
        ];
    }
}
