<?php

namespace App\Repository;

use App\Entity\Place;
use App\Repository\Interfaces\PlaceRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @package App\Repository
 */
class PlaceRepository extends BaseRepository implements PlaceRepositoryInterface
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager    = $entityManager;
        $this->conn             = $this->entityManager->getConnection();
        $this->objectRepository = $this->entityManager
                                       ->getRepository(Place::class);
    }

    public function save($entity)
    {
        parent::save($entity);
    }

    public function getOneByID(string $id): ?Place
    {
        return $this->getOneBy(["id" => $id]);
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
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
    public function getOneDefaultOrNotByUser(string $user_id): ?Place
    {
        $sql = "SELECT p
                FROM App\Entity\Place p
                WHERE p.user=:user_id AND p.is_default=true
                OR p.user=:user_id";

        $query = $this->entityManager
                        ->createQuery($sql)
                        ->setParameter('user_id', $user_id)
                        ->setMaxResults(1);

        return $query->getOneOrNullResult();
    }

    public function setAllPlacesAsNotDefault(string $user_id)
    {
        $sql = "UPDATE 
                    places 
                SET 
                    is_default=false 
                WHERE 
                    user_id=:uuid";
        $query = $this->conn->prepare($sql);

        return $query->execute(["uuid" => $user_id]);
    }
}
