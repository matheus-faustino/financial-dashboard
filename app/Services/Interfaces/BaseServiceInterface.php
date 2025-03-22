<?php

namespace App\Services\Interfaces;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Base service interface for CRUD operations
 */
interface BaseServiceInterface
{
    /**
     * Get all records
     * 
     * @param array $columns
     * @return Collection
     */
    public function getAll(array $columns = ['*']): Collection;

    /**
     * Get paginated records
     * 
     * @param int $perPage
     * @param array $columns
     * @return LengthAwarePaginator
     */
    public function getPaginated(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;

    /**
     * Create a new record
     * 
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model;

    /**
     * Update a record
     * 
     * @param array $data
     * @param int $id
     * @return bool
     */
    public function update(array $data, int $id): bool;

    /**
     * Delete a record
     * 
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Find a record by ID
     * 
     * @param int $id
     * @param array $columns
     * @return Model|null
     */
    public function findById(int $id, array $columns = ['*']): ?Model;

    /**
     * Find records with where conditions
     * 
     * @param array $conditions
     * @param array $columns
     * @return Collection
     */
    public function findWhere(array $conditions, array $columns = ['*']): Collection;
}
