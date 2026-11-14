<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        //any logged in user can create items
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:512'],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000'],
            'price_per_day' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999.99'],
            'deposit_amount' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:9999.99'],
            'categories' => ['required', 'array', 'min:1', 'max:10'],
            //every id must be an integer and exist in the categories table
            'categories.*' => ['integer', 'exists:categories,id'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    //runs after the rules above
    public function after(): array
    {
        return [
            function (Validator $validator) {
                //deposit only in free items
                if ($this->filled('deposit_amount') && (float) $this->input('price_per_day') > 0.00) {
                    $validator->errors()->add('deposit_amount', __('validation.deposit_only_free'));
                }
            },
        ];
    }
}
