<?php

namespace App\Repositories\Queries;

use App\Repositories\Contracts\JobSuggestionRepositoryInterface;
use App\Models\JobSuggestion;
use Illuminate\Pagination\LengthAwarePaginator;

class JobSuggestionRepository implements JobSuggestionRepositoryInterface
{
    /**
     * Get all job suggestions
     *
     * @param integer $perPage
     * @return LengthAwarePaginator
     */
    public function allPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return JobSuggestion::paginate($perPage);
    }

    /**
     * Get specific job suggestion
     *
     * @param integer $id
     * @return JobSuggestion|null
     */
    public function find(int $id): ?JobSuggestion
    {
        return JobSuggestion::find($id);
    }

    /**
     * Create job suggestion
     *
     * @param array $data
     * @return JobSuggestion
     */
    public function create(array $data): JobSuggestion
    {
        return JobSuggestion::create($data);
    }

    /**
     * Update job suggestion
     *
     * @param integer $id
     * @param array $data
     * @return JobSuggestion|null
     */
    public function update(int $id, array $data): ?JobSuggestion
    {
        $jobSuggestion = $this->find($id);
        if (!$jobSuggestion) return null;

        $jobSuggestion->update($data);
        return $jobSuggestion;
    }

    /**
     * Delete job suggestion
     *
     * @param integer $id
     * @return boolean
     */
    public function delete(int $id): bool
    {
        $jobSuggestion = $this->find($id);
        if (!$jobSuggestion) return false;

        return $jobSuggestion->delete();
    }

    /**
     * Toggle job suggestion upvote
     *
     * @param integer $jobSuggestionId
     * @param integer $userId
     * @return array
     */
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