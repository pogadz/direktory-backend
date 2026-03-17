<?php

namespace App\Repositories\Contracts;

interface ReviewRepositoryInterface
{
    public function paginateLatest(int $perPage = 10);

    public function findById(int $id);

    public function create(array $data);

    public function update(int $id, array $data);
}