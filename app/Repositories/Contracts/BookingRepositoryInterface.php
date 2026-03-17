<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use App\Models\Booking;

interface BookingRepositoryInterface
{
    public function allByUser(int $userId): Collection;

    public function find(int $id): ?Booking;

    public function create(array $data): Booking;

    public function update(int $id, array $data): ?Booking;

    public function setStatus(int $id, string $status): ?Booking;

    public function archive(int $id): bool;
}