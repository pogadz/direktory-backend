<?php

namespace App\Repositories\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\JobSuggestion;

interface JobSuggestionRepositoryInterface
{
    public function allPaginated(int $perPage = 10): LengthAwarePaginator;

    public function find(int $id): ?JobSuggestion;

    public function create(array $data): JobSuggestion;

    public function update(int $id, array $data): ?JobSuggestion;

    public function delete(int $id): bool;

    public function toggleUpvote(int $jobSuggestionId, int $userId): array;
}