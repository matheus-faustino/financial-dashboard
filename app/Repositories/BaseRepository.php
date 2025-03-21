<?php

namespace App\Repositories;

use App\Repositories\Interfaces\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository implements RepositoryInterface
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * Get all records
     * 
     * @param array $columns
     * @return Collection
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->model->all($columns);
    }

    /**
     * Get paginated records
     * 
     * @param int $perPage
     * @param array $columns
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->model->paginate($perPage, $columns);
    }

    /**
     * Create a new record
     * 
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Update a record
     * 
     * @param array $data
     * @param int $id
     * @return bool
     */
    public function update(array $data, int $id): bool
    {
        $record = $this->find($id);
        return $record->update($data);
    }

    /**
     * Delete a record
     * 
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->find($id)->delete();
    }

    /**
     * Find a record by ID
     * 
     * @param int $id
     * @param array $columns
     * @return Model|null
     */
    public function find(int $id, array $columns = ['*']): ?Model
    {
        return $this->model->find($id, $columns);
    }

    /**
     * Find records with where conditions
     * 
     * @param array $conditions
     * @param array $columns
     * @return Collection
     */
    public function findWhere(array $conditions, array $columns = ['*']): Collection
    {
        return $this->model->where($conditions)->get($columns);
    }
}
