<?php

namespace App\Services\Entity;

use App\Entity\Category;
use App\Entity\User;
use App\Repository\Interfaces\CategoryRepositoryInterface;
use App\Repository\Interfaces\PlaceRepositoryInterface;
use App\Services\Entity\Interfaces\CategoryServiceInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @package App\Services\Entity
 */
final class CategoryService implements CategoryServiceInterface
{
    /**
     * @var PlaceRepositoryInterface
     */
    private $repository;

    private $category;

    /**
     * @throws \Exception
     */
    public function __construct(CategoryRepositoryInterface $repository)
    {
        $this->repository = $repository;
        $this->category = new Category();
    }

    /**
     * @throws \Exception
     */
    public function getOneByUserAnyway(?User $user, array $request): Category
    {
        $category = null;

        if (!empty($request["category"]) && Uuid::isValid($request["category"])) {
            $category = $this->repository->getOneByID($request["category"]);
        }

        if (!$category && empty($request["category"])) {
            $category = $this->repository->getOneDefaultOrNotByUser($user->getId());
        }

        if (!$category) {
            $description = "";

            if (!empty($request["category"]) && !Uuid::isValid($request["category"])) {
                $description = $request["category"];
            }

            $category = new Category();

            if (!empty($description)) {
                $category->setAttributes(["description" => $description]);
            }

            $category->setUser($user);
            $this->repository->save($category);
        }

        return $category;
    }

    public function getCategoryFromUserByIdOrFail(string $user_id, string $uuid): ? Category
    {
        $category = $this->repository->getOneByUserAndID($user_id, $uuid);

        if (!$category) {
            throw new NotFoundHttpException("There is no category with this $uuid");
        }

        return $category;
    }

    public function getCategoryByIdOrFail(string $uuid): ? Category
    {
        $category = $this->repository->getOneByID($uuid);

        if (!$category) {
            throw new NotFoundHttpException("There is no category with this $uuid");
        }

        return $category;
    }

    public function getListAllByUser(string $user_id): array
    {
        $parameter["user"] = $user_id;
        return $this->repository->getAllByAndOrderedBy($parameter, ['created_at' => 'ASC']);
    }

    public function updateStatus(Category $category, string $status)
    {
        $category->changeStatusAndSetNoDefaultIfNecessary($status);

        $this->repository->save($category);
    }

    public function updateStatusDefaultSetting(Category $category, string $uuid)
    {
        $this->repository->setAllCategoriesAsNonDefault($uuid);
        $category->setDefaultAndEnableIfIsDisable();

        $this->repository->save($category);
    }

    public function logicDelete(Category $category)
    {
        $category->remove();

        $this->repository->save($category);
    }

    /**
     * @throws \Exception
     */
    public function createOrLoadCategory(?User $user, array $request): array
    {
        if (empty($request["category_id"])) {
            return $request;
        }

        $request["category"] = $this->getOneByUserAnyway($user, $request);
    }
}