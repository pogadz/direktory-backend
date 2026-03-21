<?php

namespace App\Repositories\Queries;

use App\Models\Booking;
use App\Models\Profile;
use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Services\Contracts\CreditServiceInterface;
use Illuminate\Database\Eloquent\Collection;

class BookingRepository implements BookingRepositoryInterface
{
    protected CreditServiceInterface $creditService;

    public function __construct(CreditServiceInterface $creditService)
    {
        $this->creditService = $creditService;
    }

    /**
     * Get bookings for a user with optional filters
     */
    public function allByUser(int $userId, array $filters = []): Collection
    {
        $profileIds = Profile::where('user_id', $userId)->pluck('id');

        $query = Booking::where(function ($q) use ($userId, $profileIds) {
            $q->where('user_id', $userId)
              ->orWhereIn('profile_id', $profileIds);
        });

        // Apply filters dynamically
        $query->when(isset($filters['profile_id']), function ($q) use ($filters) {
            $q->where('profile_id', $filters['profile_id']);
        });

        $query->when(isset($filters['directory_id']), function ($q) use ($filters) {
            $q->where('directory_id', $filters['directory_id']);
        });

        $query->when(isset($filters['job_category_id']), function ($q) use ($filters) {
            $q->where('job_category_id', $filters['job_category_id']);
        });

        $query->when(isset($filters['status']), function ($q) use ($filters) {
            $q->where('status', $filters['status']);
        });

        // Date filters
        $dateFields = ['requested_at', 'accepted_at', 'completed_at', 'cancelled_at', 'created_at'];
        foreach ($dateFields as $field) {
            if (!empty($filters[$field . '_from'])) {
                $query->whereDate($field, '>=', $filters[$field . '_from']);
            }
            if (!empty($filters[$field . '_to'])) {
                $query->whereDate($field, '<=', $filters[$field . '_to']);
            }
        }

        return $query->orderBy('requested_at', 'desc')->get();
    }

    /**
     * Get specific booking
     *
     * @param integer $id
     * @return Booking|null
     */
    public function find(int $id): ?Booking
    {
        return Booking::find($id);
    }

    /**
     * Create booking
     *
     * @param array $data
     * @return Booking
     */
    public function create(array $data): Booking
    {
        return Booking::create($data);
    }

    /**
     * Update booking
     *
     * @param integer $id
     * @param array $data
     * @return Booking|null
     */
    public function update(int $id, array $data): ?Booking
    {
        $booking = Booking::find($id);
        if (!$booking) return null;

        $booking->update($data);
        return $booking;
    }

    /**
     * Set booking status
     *
     * @param integer $id
     * @return bool
     */
    public function setStatus(int $id, string $status): ?Booking
    {
        $booking = Booking::find($id);
        if (!$booking) return null;

        $booking->status = $status;

        $user = $booking->profile->user;

        switch ($status) {
            case 'pending':
                $booking->requested_at = now();
                break;

            case 'accepted':
                $booking->accepted_at = now();

                // Deduct credits when booking is accepted
                $this->creditService->deduct(
                    $user,
                    20, // todo: should be dynamic in global settings
                    $booking
                );
                break;

            case 'completed':
                $booking->completed_at = now();
                break;

            case 'cancelled':
                $booking->cancelled_at = now();

                // Refund if previously deducted
                // $this->creditService->refund(
                //     $user,
                //     20, // same amount deducted
                //     $booking
                // );
                break;

            default:
                return null;
        }

        $booking->save();

        return $booking;
    }

    /**
     * Archive booking
     *
     * @param integer $id
     * @return bool
     */
    public function archive(int $id): bool
    {
        $booking = Booking::find($id);
        if (!$booking) return false;

        return $booking->delete();
    }
}