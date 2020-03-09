<?php

namespace App\Repository;

use App\Entity\User;
use App\Repository\Interfaces\UserRepositoryInterface;
use App\Utils\Enums\GeneralTypes;
use Doctrine\ORM\Query;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @package App\Repository
 */
class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager    = $entityManager;
        $this->conn             = $this->entityManager->getConnection();
        $this->objectRepository = $this->entityManager
                                       ->getRepository(User::class);
    }

    public function save($entity)
    {
        parent::save($entity);
    }

    /**
     * @return mixed|User
     */
    public function getOneUserByEmailAndStatusEnable(string $email): ?User
    {
        $parameters = [
            'email' => $email,
            'status' => GeneralTypes::STATUS_ENABLE
        ];

        return $this->getOneBy($parameters);
    }

    /**
     * @return mixed|User
     */
    public function getOneUserByEmail(string $email): ?User
    {
        $parameters = [
            'email' => $email
        ];

        return $this->getOneBy($parameters);
    }

    public function getOneByID(string $id)
    {
        $parameters = [
            'id' => $id,
            'status' => GeneralTypes::STATUS_ENABLE
        ];

        return $this->getOneBy($parameters);
    }

    public function getListOfUsersByStatusEnableAndASC(): array
    {
        $res = $this
                    ->entityManager
                    ->createQuery("
                    SELECT 
                        p.id, 
                        p.email, 
                        p.created_at,
                        p.status 
                    FROM App\Entity\User p
                    WHERE p.status = '".GeneralTypes::STATUS_ENABLE."' 
                    ORDER BY p.created_at ASC");

        return $res->getResult(Query::HYDRATE_ARRAY);
    }
}