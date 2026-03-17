<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use App\Models\Directory;

interface DirectoryRepositoryInterface
{
    public function all(): Collection;

    public function find(int $id): ?Directory;

    public function create(array $data): Directory;

    public function update(int $id, array $data): ?Directory;

    public function delete(int $id): bool;
}