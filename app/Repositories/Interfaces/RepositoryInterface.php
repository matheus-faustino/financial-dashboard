<?php

namespace App\Repositories\Interfaces;

interface RepositoryInterface
{
    /**
     * Get all records
     * 
     * @param array $columns
     * @return mixed
     */
    public function all(array $columns = ['*']);

    /**
     * Get paginated records
     * 
     * @param int $perPage
     * @param array $columns
     * @return mixed
     */
    public function paginate(int $perPage = 15, array $columns = ['*']);

    /**
     * Create a new record
     * 
     * @param array $data
     * @return mixed
     */
    public function create(array $data);

    /**
     * Update a record
     * 
     * @param array $data
     * @param int $id
     * @return mixed
     */
    public function update(array $data, int $id);

    /**
     * Delete a record
     * 
     * @param int $id
     * @return mixed
     */
    public function delete(int $id);

    /**
     * Find a record by ID
     * 
     * @param int $id
     * @param array $columns
     * @return mixed
     */
    public function find(int $id, array $columns = ['*']);

    /**
     * Find records with where conditions
     * 
     * @param array $conditions
     * @param array $columns
     * @return mixed
     */
    public function findWhere(array $conditions, array $columns = ['*']);
}
