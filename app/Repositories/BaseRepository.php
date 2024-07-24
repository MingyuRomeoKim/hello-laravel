<?php

namespace App\Repositories;

use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;

class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all()
    {
        return $this->model->all();
    }

    public function find(int $id)
    {
        return $this->model->find($id);
    }

    public function create(array $attributes)
    {
        return $this->model->create($attributes);
    }

    public function update(int $id, array $attributes)
    {
        $user = $this->find($id);

        if ($user) {
            $user->update($attributes);
            return $user;
        }

        return null;
    }

    public function delete(int $id)
    {
        $user = $this->find($id);
        if ($user) {
            return $user->delete();
        }
        return false;
    }
}
