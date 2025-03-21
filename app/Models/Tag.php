<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'user_id',
    ];

    /**
     * Get the user that owns the tag
     * 
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the transactions for this tag
     * 
     * @return BelongsToMany
     */
    public function transactions(): BelongsToMany
    {
        return $this->belongsToMany(Transaction::class);
    }

    /**
     * Scope a query to only include tags for a given user.
     *
     * @param Builder $query
     * @param int $userId
     * 
     * @return Builder
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to get tags sorted by frequency.
     *
     * @param Builder $query
     * 
     * @return Builder
     */
    public function scopeByFrequency(Builder $query): Builder
    {
        return $query->withCount('transactions')
            ->orderBy('transactions_count', 'desc');
    }

    /**
     * Scope a query to search tags by name.
     *
     * @param Builder $query
     * @param string $search
     * 
     * @return Builder
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where('name', 'like', "%{$search}%");
    }
}
