<?php

namespace App\Http\Requests\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($this->user->id),
            ],
            'manager_id' => 'nullable|integer|exists:users,id',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check if trying to set own ID as manager
            if ($this->input('manager_id') === $this->user->id) {
                $validator->errors()->add('manager_id', 'A user cannot be their own manager.');
            }

            // If user is admin, they shouldn't have a manager
            if (
                $this->user->isAdmin() &&
                $this->input('manager_id') !== null
            ) {
                $validator->errors()->add('manager_id', 'Admin users cannot have a manager.');
            }

            // Prevent assigning a manager to a manager
            if ($this->input('manager_id') !== null) {
                $manager = User::find($this->input('manager_id'));
                if ($manager && $manager->isManager() && $this->user->isManager()) {
                    $validator->errors()->add('manager_id', 'Managers cannot be managed by other managers.');
                }
            }
        });
    }
}
