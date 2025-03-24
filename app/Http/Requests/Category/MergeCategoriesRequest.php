<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;

class MergeCategoriesRequest extends FormRequest
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
            'source_id' => 'required|integer|exists:categories,id|different:target_id',
            'target_id' => 'required|integer|exists:categories,id',
        ];
    }
}
