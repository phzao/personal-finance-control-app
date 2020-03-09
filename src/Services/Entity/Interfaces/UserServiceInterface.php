<?php

namespace App\Services\Entity\Interfaces;

use App\Entity\User;

/**
 * @package App\Services\Entity\Interfaces
 */
interface UserServiceInterface
{
    public function register(array $data);

    public function getUserByEmail(string $email): ? User;

    public function updateStatus($user, string $status);

    public function getUserByIdOrFail(string $uuid): ? User;

    public function getUserByEmailAnyway(array $data): ? User;

    public function getUserByEmailToLoginOrFail(string $email): ?User;
}