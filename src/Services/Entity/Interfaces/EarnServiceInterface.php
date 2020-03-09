<?php

namespace App\Services\Entity\Interfaces;

use App\Entity\Earn;

interface EarnServiceInterface
{
    public function getListAllByUser(string $user_id): array;

    public function getEarnFromUserByIdOrFail(string $user_id, string $uuid): ?Earn;
}