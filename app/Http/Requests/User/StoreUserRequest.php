<?php

namespace App\Http\Requests\User;

use App\Enums\RoleEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'string', Password::defaults()],
            'role' => ['required', new Enum(RoleEnum::class)],
            'manager_id' => 'nullable|integer|exists:users,id',
            'is_active' => 'nullable|boolean',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // If role is admin, manager_id should be null
            if (
                $this->input('role') === RoleEnum::ADMIN->value &&
                $this->input('manager_id') !== null
            ) {
                $validator->errors()->add('manager_id', 'Admin users cannot have a manager.');
            }

            // If user is manager, they can only create users
            $currentUser = $this->user();
            if (
                $currentUser->isManager() &&
                $this->input('role') !== RoleEnum::USER->value
            ) {
                $validator->errors()->add('role', 'Managers can only create users with "user" role.');
            }
        });
    }
}
