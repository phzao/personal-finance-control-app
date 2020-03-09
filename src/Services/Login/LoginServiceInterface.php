<?php

namespace App\Services\Login;

use App\Entity\ApiToken;
use App\Entity\User;

/**
 * @package App\Services\Login
 */
interface LoginServiceInterface
{
    public function requestShouldHaveEmailAndPasswordOrFail(array $data);

    public function passwordShouldBeRightOrFail($user, string $password);

    public function checkIfExistPasswordAndIsItRightOrFail($user, array $data);

    public function getTokenCreateIfNotExist(User $user): ?ApiToken;

    public function userShouldCanAuthenticateOrFail(User $user);
}