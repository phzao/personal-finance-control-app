<?php

namespace App\Services\Entity\Interfaces;

use App\Entity\Category;
use App\Entity\User;

/**
 * @package App\Services\Entity\Interfaces
 */
interface CategoryServiceInterface
{
    public function getCategoryByIdOrFail(string $uuid);

    public function getCategoryFromUserByIdOrFail(string $user_id, string $uuid): ? Category;

    public function getOneByUserAnyway(?User $user, array $request): Category;

    public function updateStatus(Category $category, string $status);

    public function updateStatusDefaultSetting(Category $category, string $uuid);

    public function logicDelete(Category $category);

    public function getListAllByUser(string $user_id): array;

    public function createOrLoadCategory(?User $user, array $request): array;
}