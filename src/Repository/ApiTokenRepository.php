<?php

namespace App\Repository;

use App\Entity\ApiToken;
use App\Repository\Interfaces\ApiTokenRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @package App\Repository
 */
class ApiTokenRepository extends BaseRepository implements ApiTokenRepositoryInterface
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager    = $entityManager;
        $this->conn             = $this->entityManager->getConnection();
        $this->objectRepository = $this->entityManager
                                       ->getRepository(ApiToken::class);
    }

    public function save($entity)
    {
        parent::save($entity);
    }

    /**
     * @return mixed|ApiToken
     */
    public function getTheLastTokenNotExpiredByUser(string $user_id): ?ApiToken
    {
        return $this->getOneBy(["user" => $user_id, "expired_at" => null]);
    }

    /**
     * @return mixed|ApiToken
     */
    public function getOneByTokenAndNotExpired(string $token): ?ApiToken
    {
        return $this->getOneBy(['token' => $token, 'expired_at' => null]);
    }
}
