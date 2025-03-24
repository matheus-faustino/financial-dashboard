<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'payment_method' => 'required|string',
            'location' => 'nullable|string',
            'is_recurring' => 'nullable|boolean',
            'recurrence_pattern' => 'nullable|string|required_if:is_recurring,true',
            'category_id' => 'required|integer|exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
        ];
    }
}
