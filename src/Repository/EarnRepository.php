<?php

namespace App\Repository;

use App\Entity\Earn;
use App\Repository\Interfaces\EarnRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;

class EarnRepository extends BaseRepository implements EarnRepositoryInterface
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->conn = $this->entityManager->getConnection();
        $this->objectRepository = $this->entityManager
                                        ->getRepository(Earn::class);
    }

    public function getOneByUserAndID(string $user_id, string $id): ?Earn
    {
        $sql = "SELECT e
                FROM App\Entity\Earn e
                INNER JOIN App\Entity\Place p WITH p.id = e.place 
                WHERE e.id=:id AND p.user=:user_id";

        $query = $this->entityManager
                    ->createQuery($sql)
                    ->setParameter('id', $id)
                    ->setParameter('user_id', $user_id);


        return $query->getOneOrNullResult();
    }

    public function getAllByUserOrderedBy(string $user_id,
                                          $orderBy = "ORDER BY e.created_at ASC"): ?array
    {
        $sql = "SELECT e
                FROM App\Entity\Earn e
                INNER JOIN App\Entity\Place p WITH p.id = e.place 
                WHERE p.user=:user_id
                $orderBy";

        $query = $this->entityManager
                        ->createQuery($sql)
                        ->setParameter('user_id', $user_id);

        return $query->getResult(Query::HYDRATE_OBJECT);
    }
}
