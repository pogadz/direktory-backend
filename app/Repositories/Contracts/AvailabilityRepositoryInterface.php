<?php

namespace App\Repositories\Contracts;

interface AvailabilityRepositoryInterface
{
    public function getByProfileId(int $profileId);

    public function save(int $profileId, array $availabilities);
}