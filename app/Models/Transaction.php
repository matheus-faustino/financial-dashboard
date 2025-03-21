<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Transaction extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'amount',
        'date',
        'description',
        'payment_method',
        'location',
        'is_recurring',
        'recurrence_pattern',
        'user_id',
        'category_id'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'date' => 'date',
            'is_recurring' => 'boolean',
        ];
    }

    /**
     * Get the user that owns the transaction
     * 
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category of the transaction
     * 
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the tags of the transaction
     * 
     * @return BelongsToMany
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Scope a query to get only transactions for a given user
     * 
     * @param Builder $query
     * @param Builder $userId
     * 
     * @return Builder $query
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to get only transactions within a date range
     * 
     * @param Builder $query
     * @param string $startDate
     * @param string $endDate
     * 
     * @return Builder
     */
    public function scopeBetweenDates(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include transactions of a certain category type
     * 
     * @param Builder $query
     * @param string $type
     * 
     * @return Builder
     */
    public function scopeOfCategoryType(Builder $query, string $type): Builder
    {
        return $query->whereHas('category', function (Builder $subquery) use ($type) {
            return $subquery->where('type', $type);
        });
    }
}
