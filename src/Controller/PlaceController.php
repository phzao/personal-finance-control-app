<?php

namespace App\Controller;

use App\Entity\Place;
use App\Repository\Interfaces\PlaceRepositoryInterface;
use App\Services\Entity\Interfaces\PlaceServiceInterface;
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
 * @Route("/api/v1/places")
 */
class PlaceController extends APIController
{
    const MESSAGE_DUPLICATE = "Same description not allowed in more than one place!";

    /**
     * @Route("", methods={"POST"})
     */
    public function save(Request $request,
                         PlaceRepositoryInterface $repository,
                         ValidationService $validationService)
    {
        try {

            $data  = $request->request->all();
            $place = new Place();

            $place->setAttributes($data);
            $place->setUser($this->getUser());

            $validationService->entityIsValidOrFail($place);
            $repository->save($place);

            return $this->respondCreated($place->getFullData());

        } catch(UniqueConstraintViolationException $PDOException){

            return $this->respondNotAllowedError(self::MESSAGE_DUPLICATE);
        } catch (UnprocessableEntityHttpException $exception) {

            return $this->respondValidationFail($exception->getMessage());
        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }

    /**
     * @Route("/{uuid}", methods={"GET"})
     */
    public function show(string $uuid, PlaceServiceInterface $placeService)
    {
        try {

            $user = $this->getUser();
            $place = $placeService->getPlaceFromUserByIdOrFail($user->getId(), $uuid);

            return $this->respondSuccess($place->getFullData());

        } catch(ConversionException $exception)  {

          return $this->respondValidationCustomFail($exception->getMessage());
        } catch (NotFoundHttpException $exception) {
    
            return $this->respondNotFoundError($exception->getMessage());
        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }

    /**
     * @Route("", methods={"GET"})
     */
    public function list(PlaceServiceInterface $placeService)
    {
        try {
            $user = $this->getUser();
            $list = $placeService->getListAllByUser($user->getId());

            return $this->respondSuccess($list);

        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }

    /**
     * @Route("/{uuid}", methods={"PUT"})
     */
    public function update(Request $request,
                           string $uuid,
                           PlaceServiceInterface $placeService,
                           PlaceRepositoryInterface $repository,
                           ValidationService $validationService)
    {
        try {

            $user = $this->getUser();

            $place = $placeService->getPlaceFromUserByIdOrFail($user->getId(), $uuid);
            $data  = $request->request->all();

            $place->setAttributes($data);
            $validationService->entityIsValidOrFail($place);
            $repository->save($place);

            return $this->respondUpdatedResource();

        } catch(UniqueConstraintViolationException $PDOException){

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
     * @Route("/{uuid}/default", methods={"PUT"})
     */
    public function updateDefaultStatus(string $uuid,
                                        PlaceServiceInterface $placeService)
    {
        try {
            $user = $this->getUser();

            $place = $placeService->getPlaceFromUserByIdOrFail($user->getId(), $uuid);
            $placeService->updateStatusDefaultSetting($place, $user->getId());

            return $this->respondUpdatedResource();

        } catch (NotFoundHttpException $exception) {

            return $this->respondNotFoundError($exception->getMessage());
        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }

    /**
     * @Route("/{uuid}/status/{status}", methods={"PUT"})
     */
    public function updateStatus(string $uuid,
                                 string $status,
                                 PlaceServiceInterface $placeService)
    {
        try {
            $user = $this->getUser();
            GeneralTypes::isValidDefaultStatusOrFail($status);
            $place = $placeService->getPlaceFromUserByIdOrFail($user->getId(), $uuid);
            $placeService->updateStatus($place, $status);

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
                           PlaceServiceInterface $placeService)
    {
        try {
            $user = $this->getUser();

            $place = $placeService->getPlaceFromUserByIdOrFail($user->getId(), $uuid);

            $placeService->logicDelete($place);

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