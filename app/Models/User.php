<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\RoleEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'manager_id',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'role' => RoleEnum::class,
        ];
    }

    /**
     * Get the manager if this user 
     * 
     * @return BelongsTo
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get the users managed by this user
     * 
     * @return HasMany
     */
    public function managedUsers(): HasMany
    {
        return $this->hasMany(User::class, 'manager_id');
    }

    /**
     * Get the transactions for this user
     * 
     * @return HasMany
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Check if the user is an administrator
     * 
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role->value == RoleEnum::ADMIN->value;
    }

    /**
     * Check if the user is an manager
     * 
     * @return bool
     */
    public function isManager(): bool
    {
        return $this->role->value == RoleEnum::MANAGER->value;
    }

    /**
     * Check if the user is an user
     * 
     * @return bool
     */
    public function isUser(): bool
    {
        return $this->role->value == RoleEnum::USER->value;
    }
}
