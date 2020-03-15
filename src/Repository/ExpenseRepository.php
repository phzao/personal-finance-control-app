<?php

namespace App\Repository;

use App\Entity\Expense;
use App\Repository\Interfaces\ExpenseRepositoryInterface;
use Doctrine\ORM\AbstractQuery;
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

    public function listAllBy(array $params, $orderBy = "created_at", $order = "ASC"): array
    {
        $description = empty($params["description"])? "":"AND e.description ILIKE :description ";
        $status = empty($params["status"]) ? "" : "AND e.status=:status ";
        $dueDate = empty($params["due_date"]) ? "" : "AND e.due_date<=:due_date ";
        $deletedAt = !key_exists("deleted_at", $params) ? "" : "AND e.deleted_at IS NULL ";

        $res = $this->entityManager->createQuery("SELECT e 
                                              FROM App\Entity\Expense e
                                              WHERE e.registered_by=:registered_by 
                                                $description 
                                                $status
                                                $dueDate
                                                $deletedAt
                                              ORDER BY e.".$orderBy." $order");

        $res->setParameter('registered_by', $params["registered_by"]);

        if(!empty($params["description"])) {
            $res->setParameter('description', $params["description"]."%");
        }

        if(!empty($params["status"])) {
            $res->setParameter('status', $params["status"]);
        }

        if(!empty($params["due_date"])) {
            $res->setParameter('due_date', $params["due_date"]);
        }

        return $res->getResult();
    }

    public function getDataToPieChartByCategoryAndDueDate(array $params): array
    {
        $res = $this->conn->prepare(
            "SELECT 
                            res.sum AS total,
                            res.count AS num_expenses,
                            res.description AS description,
                            res.id AS id
                        FROM
                        (SELECT
                            SUM(ex.value),
                            COUNT(*),
                            cat.id,
                            cat.description
                        FROM 
                            public.expenses AS ex
                        INNER JOIN categories AS cat ON cat.id = ex.category_id
                        WHERE ex.registered_by_id=:registered_by
                        AND ex.deleted_at IS NULL
                        AND
                            ex.due_date >=:start_date AND ex.due_date <=:end_date
                        GROUP BY cat.description, cat.id
                        ORDER BY cat.description) AS res"
        );

        $res->bindValue('registered_by', $params["registered_by"]);
        $res->bindValue('start_date', $params["startDate"]);
        $res->bindValue('end_date', $params["endDate"]);

        $res->execute();

        return $res->fetchAll();
    }

    public function getLast12MonthsStatisticsByCategoryAndDueDate(array $params): array
    {
        $res = $this->conn->prepare(
            "SELECT
                        (SELECT 
                                SUM(ex.value)
                            FROM 
                                expenses AS ex
                            WHERE 
                                CAST(ex.due_date AS TEXT) LIKE categories.year_month||'-%' 
                            AND ex.category_id = categories.id) AS total,
                        categories.description,
                        categories.id,
                        categories.year_month,
                        UPPER(to_char(to_timestamp (SPLIT_PART(categories.year_month,'-',2)::text, 'MM'), 'TMmon'))||'/'||SPLIT_PART(categories.year_month,'-',1) AS reference
                        FROM
                            (SELECT 
                                distinct cat.id, 
                                 cat.description,
                                CAST((extract(year FROM due_date))||'-'||lpad(extract(month FROM due_date)::text, 2, '0') AS TEXT) AS year_month
                            FROM 
                                expenses
                            INNER JOIN categories AS cat ON cat.id = category_id
                            WHERE
                                registered_by_id=:registered_by
                            AND
                                expenses.due_date > CURRENT_DATE - INTERVAL '12 months'
                            AND
                                expenses.deleted_at IS NULL
                              ) AS categories"
                            );

        $res->bindValue('registered_by', $params["registered_by"]);

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
