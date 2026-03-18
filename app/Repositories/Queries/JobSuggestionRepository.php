<?php

namespace App\Repositories\Queries;

use App\Repositories\Contracts\JobSuggestionRepositoryInterface;
use App\Models\JobSuggestion;
use Illuminate\Pagination\LengthAwarePaginator;

class JobSuggestionRepository implements JobSuggestionRepositoryInterface
{
    public function allPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return JobSuggestion::paginate($perPage);
    }

    public function find(int $id): ?JobSuggestion
    {
        return JobSuggestion::find($id);
    }

    public function create(array $data): JobSuggestion
    {
        return JobSuggestion::create($data);
    }

    public function update(int $id, array $data): ?JobSuggestion
    {
        $jobSuggestion = $this->find($id);
        if (!$jobSuggestion) return null;

        $jobSuggestion->update($data);
        return $jobSuggestion;
    }

    public function delete(int $id): bool
    {
        $jobSuggestion = $this->find($id);
        if (!$jobSuggestion) return false;

        return $jobSuggestion->delete();
    }

    public function toggleUpvote(int $jobSuggestionId, int $userId): array
    {
        $jobSuggestion = $this->find($jobSuggestionId);
        if (!$jobSuggestion) return ['error' => 'Job suggestion not found'];

        if ($jobSuggestion->upvoters()->where('user_id', $userId)->exists()) {
            $jobSuggestion->upvoters()->detach($userId);
            $upvoted = false;
        } else {
            $jobSuggestion->upvoters()->attach($userId);
            $upvoted = true;
        }

        $jobSuggestion->update(['upvotes' => $jobSuggestion->upvoters()->count()]);

        return [
            'jobSuggestion' => $jobSuggestion,
            'upvoted' => $upvoted
        ];
    }
}