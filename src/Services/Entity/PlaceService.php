<?php

namespace App\Services\Entity;

use App\Entity\Interfaces\PlaceInterface;
use App\Entity\Place;
use App\Entity\User;
use App\Repository\Interfaces\PlaceRepositoryInterface;
use App\Services\Entity\Interfaces\PlaceServiceInterface;
use App\Utils\Enums\GeneralTypes;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @package App\Services\Entity
 */
final class PlaceService implements PlaceServiceInterface
{
    /**
     * @var PlaceRepositoryInterface
     */
    private $repository;

    /**
     * @var PlaceInterface
     */
    private $place;

    /**
     * @throws \Exception
     */
    public function __construct(PlaceRepositoryInterface $repository)
    {
        $this->repository = $repository;
        $this->place = new Place();
    }

    public function getPlaceFromUserByIdOrFail(string $user_id, string $uuid): ? Place
    {
        $place = $this->repository->getOneBy(["user" => $user_id, "id" => $uuid]);

        if (!$place) {
            throw new NotFoundHttpException("There is no place with this $uuid");
        }

        return $place;
    }

    public function getListAllBy(array $params, string $user_id): array
    {
        $parameter["user"] = $user_id;
        return $this->repository->getAllBy($parameter);
    }

    public function getListAllByUser(string $user_id): array
    {
        $parameter["user"] = $user_id;
        return $this->repository->getAllByAndOrderedBy($parameter, ['created_at' => 'ASC']);
    }

    public function updateStatus(Place $place, string $status)
    {
        $place->setAttribute('status', $status);
        $place->setAttribute('is_default', GeneralTypes::DEFAULT_UNSET);

        $this->repository->save($place);
    }

    /**
     * @throws \Exception
     */
    public function getOneByUserAnyway(?User $user, array $request): ? Place
    {
        $place = null;

        if (!empty($request["place"]) && Uuid::isValid($request["place"])) {
            $place = $this->repository->getOneByID($request["place"]);
        }

        if (!$place && empty($request["place"])) {
            $place = $this->repository->getOneDefaultOrNotByUser($user->getId());
        }

        if (!$place) {
            $description = "";

            if (!empty($request["place"]) && !Uuid::isValid($request["place"])) {
                $description = $request["place"];
            }

            $place = new Place();

            if (!empty($description)) {
                $place->setAttributes(["description" => $description]);
            }

            $place->setUser($user);
            $this->repository->save($place);
        }

        return $place;
    }

    public function updateStatusDefaultSetting(Place $place, string $uuid)
    {
        $this->repository->setAllPlacesAsNotDefault($uuid);
        $place->setDefault();

        $this->repository->save($place);
    }

    public function logicDelete(Place $place)
    {
        $place->remove();

        $this->repository->save($place);
    }

    public function getPlaceIfWasPassedOrFail(array $data, string $user_id)
    {
        if (empty($data["place"])) {
            return $data;
        }

        return $this->getPlaceFromUserByIdOrFail($user_id, $data["place"]);
    }

    /**
     * @throws \Exception
     */
    public function createOrLoadPlace(?User $user, array $request): array
    {
        if (empty($request["place_id"])) {
            return $request;
        }

        $request["place"] = $this->getOneByUserAnyway($user, $request);
    }
}