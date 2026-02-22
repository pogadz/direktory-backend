<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\Role;
use Illuminate\Http\Request;

class WorkerController extends Controller
{
    /**
     * @group Workers
     *
     * Filter and list worker profiles.
     * @unauthenticated
     *
     * Returns active profiles that have the worker role.
     *
     * @queryParam job_category_id integer Filter by job category ID. Example: 1
     * @queryParam search string Search by profile name or bio. Example: plumber
     * @queryParam per_page integer Number of results per page (default 15, max 100). Example: 15
     *
     * @response 200 {
     *   "data": [],
     *   "meta": { "current_page": 1, "per_page": 15, "total": 0 }
     * }
     */
    public function index(Request $request)
    {
        $request->validate([
            'job_category_id' => ['nullable', 'integer', 'exists:job_categories,id'],
            'search'          => ['nullable', 'string', 'max:100'],
            'per_page'        => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $workerRole = Role::where('name', 'worker')->first();

        if (!$workerRole) {
            return response()->json(['data' => [], 'meta' => ['total' => 0]], 200);
        }

        $query = Profile::query()
            ->where('is_active', true)
            ->whereHas('roles', fn($q) => $q->where('roles.id', $workerRole->id))
            ->with(['jobCategory', 'user:id,firstname,lastname']);

        if ($request->filled('job_category_id')) {
            $query->where('job_category_id', $request->job_category_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('bio', 'like', "%{$search}%");
            });
        }

        $perPage = min($request->integer('per_page', 15), 100);
        $workers = $query->paginate($perPage);

        return response()->json([
            'data' => $workers->map(fn($profile) => [
                'id'           => $profile->id,
                'name'         => $profile->name,
                'bio'          => $profile->bio,
                'avatar'       => $profile->avatar,
                'address'      => $profile->address,
                'job_category' => $profile->jobCategory ? [
                    'id'   => $profile->jobCategory->id,
                    'name' => $profile->jobCategory->name,
                ] : null,
                'user' => [
                    'id'        => $profile->user->id,
                    'firstname' => $profile->user->firstname,
                    'lastname'  => $profile->user->lastname,
                ],
            ]),
            'meta' => [
                'current_page' => $workers->currentPage(),
                'per_page'     => $workers->perPage(),
                'total'        => $workers->total(),
                'last_page'    => $workers->lastPage(),
            ],
        ]);
    }

    /**
     * @group Workers
     *
     * Get a single worker profile.
     * @unauthenticated
     *
     * @urlParam id integer required The profile ID. Example: 1
     *
     * @response 404 { "message": "Worker not found." }
     */
    public function show(int $id)
    {
        $workerRole = Role::where('name', 'worker')->first();

        $profile = Profile::query()
            ->where('id', $id)
            ->where('is_active', true)
            ->whereHas('roles', fn($q) => $q->where('roles.id', $workerRole?->id ?? 0))
            ->with(['jobCategory', 'user:id,firstname,lastname'])
            ->first();

        if (!$profile) {
            return response()->json(['message' => 'Worker not found.'], 404);
        }

        return response()->json([
            'data' => [
                'id'           => $profile->id,
                'name'         => $profile->name,
                'bio'          => $profile->bio,
                'avatar'       => $profile->avatar,
                'address'      => $profile->address,
                'job_category' => $profile->jobCategory ? [
                    'id'   => $profile->jobCategory->id,
                    'name' => $profile->jobCategory->name,
                ] : null,
                'user' => [
                    'id'        => $profile->user->id,
                    'firstname' => $profile->user->firstname,
                    'lastname'  => $profile->user->lastname,
                ],
            ],
        ]);
    }
}
