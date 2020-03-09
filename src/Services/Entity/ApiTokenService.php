<?php

namespace App\Services\Entity;

use App\Entity\ApiToken;
use App\Entity\User;
use App\Repository\Interfaces\ApiTokenRepositoryInterface;
use App\Services\Entity\Interfaces\ApiTokenServiceInterface;
use App\Utils\Generators\TokenGeneratorInterface;

/**
 * @package App\Services\Entity
 */
class ApiTokenService implements ApiTokenServiceInterface
{
    private $repository;

    private $tokenGenerator;

    public function __construct(ApiTokenRepositoryInterface $apiTokenRepository, TokenGeneratorInterface $tokenGenerator)
    {
        $this->repository = $apiTokenRepository;
        $this->tokenGenerator = $tokenGenerator;
    }

    /**
     * @throws \Exception
     */
    public function registerAndGetApiTokenTo(User $user): ApiToken
    {
        $apiToken = new ApiToken();
        $apiToken->setUser($user);
        $apiToken->generateToken($this->tokenGenerator);
        $this->repository->save($apiToken);

        return $apiToken;
    }

    public function getAValidApiTokenToUser(string $user_id): ?ApiToken
    {
        return $this->repository->getTheLastTokenNotExpiredByUser($user_id);
    }
}