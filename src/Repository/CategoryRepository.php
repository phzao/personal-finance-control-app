<?php

namespace App\Repository;

use App\Entity\Category;
use App\Repository\Interfaces\CategoryRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @package App\Repository
 */
class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->conn = $this->entityManager->getConnection();
        $this->objectRepository = $this->entityManager
                                       ->getRepository(Category::class);
    }

    public function getOneByID(string $id): ?Category
    {
        return $this->getOneBy(["id" => $id]);
    }

    public function getOneByDescription(string $user_id, string $description): ?Category
    {

        $sql = "SELECT c
                FROM App\Entity\Category c
                WHERE c.user=:user_id
                AND c.description LIKE :description ";
        $query = $this->entityManager
                        ->createQuery($sql)
                        ->setParameter('user_id', $user_id)
                        ->setParameter('description', "$description%")
                        ->setMaxResults(1);

        return $query->getOneOrNullResult();
    }

    public function getOneByUserAndID(string $user_id, string $id): ?Category
    {
        return $this->getOneBy(["user" => $user_id, "id" => $id]);
    }

    public function getAllNotDeletedByUser(string $user_id): ?array
    {
        return $this->getAllBy(["user" => $user_id, "deleted_at" => null]);
    }

    public function listAllBy(array $params, $orderBy = "created_at", $order = "ASC"): array
    {

        return $this->objectRepository->findBy($params,[$orderBy=>$order]);
    }

    public function save($entity)
    {
        parent::save($entity);
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getOneDefaultOrNotByUser(string $user_id): ?Category
    {
        $sql = "SELECT c
                FROM App\Entity\Category c
                WHERE c.user=:user_id AND c.is_default = true
                OR c.user=:user_id";
        $query = $this->entityManager
                      ->createQuery($sql)
                      ->setParameter('user_id', $user_id)
                      ->setMaxResults(1);

        return $query->getOneOrNullResult();
    }

    public function setAllCategoriesAsNonDefault(string $user_id)
    {
        $sql = "UPDATE 
                    categories 
                SET 
                    is_default=false 
                WHERE 
                    user_id=:uuid";
        $query = $this->conn->prepare($sql);

        return $query->execute(["uuid" => $user_id]);
    }
}
