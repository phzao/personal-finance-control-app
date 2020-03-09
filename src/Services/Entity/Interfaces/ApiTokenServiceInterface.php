<?php

namespace App\Services\Entity\Interfaces;

use App\Entity\ApiToken;
use App\Entity\User;

/**
 * @package App\Services\Entity\Interfaces
 */
interface ApiTokenServiceInterface
{
    public function registerAndGetApiTokenTo(User $user): ApiToken;

    public function getAValidApiTokenToUser(string $user_id): ?ApiToken;
}