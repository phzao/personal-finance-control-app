<?php

namespace App\Repository;

use App\Entity\Expense;
use App\Repository\Interfaces\ExpenseRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @package App\Repository
 */
class ExpenseRepository extends BaseRepository implements ExpenseRepositoryInterface
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager    = $entityManager;
        $this->conn             = $this->entityManager->getConnection();
        $this->objectRepository = $this->entityManager
                                        ->getRepository(Expense::class);
    }

    public function save($entity)
    {
        parent::save($entity);
    }

    public function remove($entity)
    {
        parent::remove($entity);
    }

    public function getByID(string $id): ?Expense
    {
        return $this->getOneBy(["id" => $id]);
    }

    public function listAllBy(array $params,
                              $orderBy = "created_at",
                              $order = "ASC"): array
    {
        $description = empty($params["description"])? "":"AND p.description ILIKE :description ";
        $status      = empty($params["status"])?"":"AND p.status=:status ";

        $res = $this->conn->prepare("SELECT * 
                                              FROM places p
                                              WHERE p.user_id=:user_id 
                                                $description 
                                                $status
                                              ORDER BY p.".$orderBy." $order");

        $res->bindValue('user_id', $params["user"]);

        if(!empty($params["description"])) {
            $res->bindValue('description', $params["description"]."%");
        }

        if(!empty($params["status"])) {
            $res->bindValue('status', $params["status"]);
        }

        $res->execute();

        return $res->fetchAll();
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getTheLastRecordByUser(string $user_id): ? Expense
    {
        $sql = "SELECT e
                FROM App\Entity\Expense e
                WHERE e.registered_by=:user_id
                ORDER BY e.created_at DESC";
        $query = $this->entityManager
                    ->createQuery($sql)
                    ->setParameter('user_id', $user_id)
                    ->setMaxResults(1);

        return $query->getOneOrNullResult();
    }

    public function setAllExpensesDeletedAtByToken(string $token, string $deleted_date)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder->update("App\Entity\Expense", "e");
        $queryBuilder->set('e.deleted_at',':deleted_date');
        $queryBuilder->setParameter('deleted_date', $deleted_date);

        if (!empty($token)) {
            $queryBuilder->where('e.token_installment_group =:token');
            $queryBuilder->setParameter('token', $token);
        }

        $queryBuilder->getQuery()->execute();
    }

    public function getOneByRegisteredAndID(string $user_id, string $uuid): ?Expense
    {
        $params = [
            "registered_by" => $user_id,
            "id" => $uuid
        ];

        return $this->getOneBy($params);
    }
}
