<?php

namespace App\Repositories;

interface BaseRepositoryInterface
{
    public function all();
    public function find(int $id);
    public function create(array $attributes);
    public function update(int $id, array $attributes);
    public function delete(int $id);
}
