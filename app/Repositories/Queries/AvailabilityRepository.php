<?php

namespace App\Repositories\Queries;

use App\Models\Availability;
use App\Repositories\Contracts\AvailabilityRepositoryInterface;

class AvailabilityRepository implements AvailabilityRepositoryInterface
{
    public function getByProfileId(int $profileId)
    {
        $availability = Availability::where('profile_id', $profileId)->first();

        if (!$availability) {
            return null;
        }

        return [
            'profile_id' => $availability->profile_id,
            'availabilities' => $this->transformSchedule($availability->schedule)
        ];
    }

    public function save(int $profileId, array $availabilities)
    {
        $schedule = [];
        $count = count($availabilities);

        foreach ($availabilities as $dayData) {
            $day = $dayData['day'] ?? '';

            // If multiple items → ignore empty day entries
            if ($count > 1 && empty($day)) {
                continue;
            }

            // If only one item and day is empty → store as empty object {}
            if ($count === 1 && empty($day)) {
                $schedule = new \stdClass();
                break;
            }

            if (!empty($day)) {
                $schedule[$day] = [
                    'open' => $dayData['open'] ?? null,
                    'close' => $dayData['close'] ?? null,
                    'enabled' => $dayData['enabled'] ?? false,
                ];
            }
        }

        $availability = Availability::updateOrCreate(
            ['profile_id' => $profileId],
            ['schedule' => $schedule]
        );

        return [
            'profile_id' => $availability->profile_id,
            'availabilities' => $this->transformSchedule($availability->schedule)
        ];
    }

    private function transformSchedule($schedule)
    {
        $formatted = [];

        // If schedule is empty object {}
        if (is_object($schedule) && empty((array) $schedule)) {
            return [];
        }

        foreach ($schedule as $day => $data) {
            $formatted[] = [
                'day' => $day,
                'open' => $data['open'] ?? null,
                'close' => $data['close'] ?? null,
                'enabled' => $data['enabled'] ?? false,
            ];
        }

        return $formatted;
    }
}
