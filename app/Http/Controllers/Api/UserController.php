<?php

namespace App\Http\Controllers\API;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\ChangePasswordRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateStatusRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use App\Services\Interfaces\UserServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;

        $this->authorizeResource(User::class);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $activeOnly = request()->query('active_only', false);

        if ($activeOnly) {
            $users = $this->userService->getActiveUsers();
        } else {
            $users = $this->userService->getAll();
        }

        return response()->json(['users' => $users]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->registerUser($request->validated(), Auth::id());

        return response()->json(['user' => $user], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): JsonResponse
    {
        return response()->json(['user' => $user]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $this->userService->update($request->validated(), $user->id);

        return response()->json(['user' => $user->fresh()]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): JsonResponse
    {
        $this->userService->delete($user->id);

        return response()->json(['message' => 'User deleted successfully'], 200);
    }

    /**
     * Get users by role
     */
    public function byRole(string $role): JsonResponse
    {
        $validRoles = [
            RoleEnum::ADMIN->value,
            RoleEnum::MANAGER->value,
            RoleEnum::USER->value
        ];

        if (!in_array($role, $validRoles)) {
            return response()->json(['message' => 'Invalid role'], 400);
        }

        $users = $this->userService->getUsersByRole(RoleEnum::from($role));

        return response()->json(['users' => $users]);
    }

    /**
     * Get users by manager
     */
    public function byManager(int $managerId): JsonResponse
    {
        $users = $this->userService->getUsersByManager($managerId);

        return response()->json(['users' => $users]);
    }

    /**
     * Update user status (active/inactive)
     */
    public function updateStatus(UpdateStatusRequest $request, User $user): JsonResponse
    {
        if ($user->id === Auth::id()) {
            return response()->json(['message' => 'You cannot deactivate your own account'], 403);
        }

        $isActive = $request->is_active;
        $this->userService->updateUserStatus($user->id, $isActive);

        $status = $isActive ? 'activated' : 'deactivated';

        return response()->json(['message' => "User {$status} successfully"]);
    }

    /**
     * Change user password
     */
    public function changePassword(ChangePasswordRequest $request, User $user): JsonResponse
    {
        Gate::authorize('resetPassword', $user);

        $this->userService->changePassword($user->id, $request->password);

        return response()->json(['message' => 'Password changed successfully']);
    }
}
