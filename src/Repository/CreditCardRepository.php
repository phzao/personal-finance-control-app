<?php

namespace App\Repository;

use App\Entity\CreditCard;
use App\Repository\Interfaces\CreditCardRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class CreditCardRepository extends BaseRepository implements CreditCardRepositoryInterface
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->conn = $this->entityManager->getConnection();
        $this->objectRepository = $this->entityManager
                                        ->getRepository(CreditCard::class);
    }

    public function getOneByUserAndID(string $user_id, string $id): ?CreditCard
    {
        return $this->getOneBy(["user" => $user_id, "id" => $id]);
    }

    public function setAllCreditCardsAsNonDefault(string $user_id)
    {
        $sql = "UPDATE 
                    credit_cards 
                SET 
                    is_default=false 
                WHERE 
                    user_id=:uuid";
        $query = $this->conn->prepare($sql);

        return $query->execute(["uuid" => $user_id]);
    }

    public function getOneById(string $id): ?CreditCard
    {
        return $this->getOneBy(["id" => $id]);
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getOneDefaultOrNotByUser(string $user_id): ?CreditCard
    {
        $sql = "SELECT c
                FROM App\Entity\CreditCard c
                WHERE c.user=:user_id AND c.is_default = true
                OR c.user=:user_id";
        $query = $this->entityManager
            ->createQuery($sql)
            ->setParameter('user_id', $user_id)
            ->setMaxResults(1);

        return $query->getOneOrNullResult();
    }

    public function getAllNotDeletedAndOrderedBy(array $parameters): ?array
    {
        return $this->getAllByAndOrderedBy($parameters);
    }
}
