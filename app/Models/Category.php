<?php

namespace App\Models;

use App\Enums\CategoryTypeEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'type',
        'color',
        'is_system',
        'user_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'type' => CategoryTypeEnum::class,
            'is_system' => 'boolean',
        ];
    }

    /**
     * Get the user that owns the category
     * 
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the transactions for this category
     * 
     * @return HasMany
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Scope a query to only include categories for a given user and system categories
     * 
     * @param Builder $query
     * @param int $userId
     * 
     * @return Builder
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where(function (Builder $subquery) use ($userId) {
            return $subquery->where('user_id', $userId)
                ->orWhere('is_system', true);
        });
    }

    /**
     * Scope a query to only include categories of a specific type.
     *
     * @param Builder $query
     * @param string $type
     * 
     * @return Builder
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include system categories.
     *
     * @param Builder $query
     * 
     * @return Builder
     */
    public function scopeSystem(Builder $query): Builder
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope a query to only include custom (user-created) categories.
     *
     * @param Builder $query
     * 
     * @return Builder
     */
    public function scopeCustom(Builder $query): Builder
    {
        return $query->where('is_system', false);
    }
}
