<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\Interfaces\CategoryRepositoryInterface;
use App\Services\Entity\Interfaces\CategoryServiceInterface;
use App\Services\Validation\ValidationService;
use App\Utils\Enums\GeneralTypes;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\DBAL\Types\ConversionException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @package App\Controller
 * @Route("/api/v1/categories")
 */
class CategoryController extends APIController
{
    const MESSAGE_DUPLICATE = "Same description not allowed in more than one category!";

    /**
     * @Route("", methods={"POST"})
     */
    public function save(Request $request,
                         ValidationService $validationService,
                         CategoryRepositoryInterface $repository)
    {
        try {
            $data  = $request->request->all();
            $category = new Category();
            $data["user"] = $this->getUser();

            $category->setAttributes($data);

            $validationService->entityIsValidOrFail($category);
            $repository->save($category);

            return $this->respondCreated($category->getFullData());

        } catch(UniqueConstraintViolationException $PDOException){

            return $this->respondNotAllowedError(self::MESSAGE_DUPLICATE);
        } catch (UnprocessableEntityHttpException $exception) {

            return $this->respondValidationFail($exception->getMessage());
        }  catch (NotFoundHttpException $exception) {

            return $this->respondNotFoundError($exception->getMessage());
        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }

    /**
     * @Route("/{uuid}", methods={"GET"})
     */
    public function show(string $uuid, CategoryServiceInterface $categoryService)
    {
        try {
            $user = $this->getUser();

            $category = $categoryService->getCategoryFromUserByIdOrFail($user->getId(), $uuid);

            return $this->respondSuccess($category->getFullData());

        } catch(ConversionException $exception)  {

            return $this->respondValidationCustomFail($exception->getMessage());
        } catch (NotFoundHttpException $exception) {

            return $this->respondNotFoundError($exception->getMessage());
        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }

    /**
     * @Route("/{uuid}", methods={"PUT"})
     */
    public function update(string $uuid,
                           Request $request,
                           ValidationService $validationService,
                           CategoryRepositoryInterface $categoryRepository,
                           CategoryServiceInterface $categoryService)
    {
        try {
            $user = $this->getUser();
            $data = $request->request->all();

            $category = $categoryService->getCategoryFromUserByIdOrFail($user->getId(), $uuid);

            $category->setAttributes($data);
            $validationService->entityIsValidOrFail($category);
            $categoryRepository->save($category);

            return $this->respondUpdatedResource();

        }catch(UniqueConstraintViolationException $PDOException){

            return $this->respondNotAllowedError(self::MESSAGE_DUPLICATE);
        } catch(ConversionException $exception)  {

            return $this->respondValidationCustomFail($exception->getMessage());
        } catch (UnprocessableEntityHttpException $exception) {

            return $this->respondValidationFail($exception->getMessage());
        }  catch (NotFoundHttpException $exception) {

            return $this->respondNotFoundError($exception->getMessage());
        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }

    /**
     * @Route("", methods={"GET"})
     */
    public function list(CategoryServiceInterface $categoryService)
    {
        try {
            $user = $this->getUser();
            $list = $categoryService->getListAllByUser($user->getId());

            return $this->respondSuccess($list);

        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }

    /**
     * @Route("/{uuid}/status/{status}", methods={"PUT"})
     */
    public function updateStatus(string $uuid,
                                 string $status,
                                 CategoryServiceInterface $categoryService)
    {
        try {
            $user = $this->getUser();
            GeneralTypes::isValidDefaultStatusOrFail($status);

            $category = $categoryService->getCategoryFromUserByIdOrFail($user->getId(), $uuid);
            $categoryService->updateStatus($category, $status);

            return $this->respondUpdatedResource();

        } catch(ConversionException $exception)  {

            return $this->respondValidationCustomFail($exception->getMessage());
        } catch (UnprocessableEntityHttpException $exception) {

            return $this->respondValidationFail($exception->getMessage());
        }  catch (NotFoundHttpException $exception) {

            return $this->respondNotFoundError($exception->getMessage());
        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }

    /**
     * @Route("/{uuid}/default", methods={"PUT"})
     */
    public function updateDefaultStatus(string $uuid,
                                        CategoryServiceInterface $categoryService)
    {
        try {
            $user = $this->getUser();

            $category = $categoryService->getCategoryFromUserByIdOrFail($user->getId(), $uuid);
            $categoryService->updateStatusDefaultSetting($category, $user->getId());

            return $this->respondUpdatedResource();

        } catch(ConversionException $exception)  {

            return $this->respondValidationCustomFail($exception->getMessage());
        } catch (UnprocessableEntityHttpException $exception) {

            return $this->respondValidationFail($exception->getMessage());
        }  catch (NotFoundHttpException $exception) {

            return $this->respondNotFoundError($exception->getMessage());
        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }

    /**
     * @Route("/{uuid}", methods={"DELETE"})
     */
    public function delete(string $uuid,
                           CategoryServiceInterface $categoryService)
    {
        try {
            $user = $this->getUser();

            $category = $categoryService->getCategoryFromUserByIdOrFail($user->getId(), $uuid);

            $categoryService->logicDelete($category);

            return $this->respondUpdatedResource();

        } catch(ConversionException $exception)  {

            return $this->respondValidationCustomFail($exception->getMessage());
        } catch (NotFoundHttpException $exception) {

            return $this->respondNotFoundError($exception->getMessage());
        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }
}