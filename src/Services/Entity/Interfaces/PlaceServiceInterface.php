<?php

namespace App\Services\Entity\Interfaces;

use App\Entity\Place;
use App\Entity\User;

/**
 * @package App\Services\Entity\Interfaces
 */
interface PlaceServiceInterface
{
    public function getPlaceFromUserByIdOrFail(string $user_id, string $uuid): ? Place;

    public function getListAllBy(array $params, string $user_id): array;

    public function getListAllByUser(string $user_id): array;

    public function updateStatus(Place $place, string $status);

    public function getOneByUserAnyway(?User $user, array $request): ? Place;

    public function updateStatusDefaultSetting(Place $place, string $uuid);

    public function logicDelete(Place $place);

    public function getPlaceIfWasPassedOrFail(array $data, string $user_id);

    public function createOrLoadPlace(?User $user, array $request): array;
}