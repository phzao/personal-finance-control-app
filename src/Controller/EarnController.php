<?php

namespace App\Controller;

use App\Entity\Earn;
use App\Repository\Interfaces\EarnRepositoryInterface;
use App\Services\Entity\Interfaces\EarnServiceInterface;
use App\Services\Entity\Interfaces\PlaceServiceInterface;
use App\Services\Validation\ValidationService;
use App\Utils\Datetime\Interfaces\DatetimeCheckServiceInterface;
use Doctrine\DBAL\Types\ConversionException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @package App\Controller
 * @Route("/api/v1/earns")
 */
class EarnController extends APIController
{
    /**
     * @Route("", methods={"POST"})
     */
    public function save(Request $request,
                         ValidationService $validationService,
                         EarnRepositoryInterface $repository,
                         PlaceServiceInterface $placeService,
                         DatetimeCheckServiceInterface $dateService)
    {
        try {
            $data  = $request->request->all();
            $earn = new Earn();
            $data["user"] = $this->getUser();
            $data["place"] = $placeService->getOneByUserAnyway($this->getUser(), $data);

            $datesAttribute = $earn->getAllAttributesDateAndFormat();
            $data = $dateService->getDatesListConvertedToDatetimeOrFail($datesAttribute,
                                                                        $data);

            $earn->setAttributes($data);
            $validationService->entityIsValidOrFail($earn);

            $repository->save($earn);

            return $this->respondCreated($earn->getFullData());

        } catch (UnprocessableEntityHttpException $exception) {

            return $this->respondValidationFail($exception->getMessage());
        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }

    /**
     * @Route("/{uuid}", methods={"GET"})
     */
    public function show(string $uuid, EarnServiceInterface $earnService)
    {
        try {
            $user = $this->getUser();

            $category = $earnService->getEarnFromUserByIdOrFail($user->getId(), $uuid);

            return $this->respondSuccess($category->getFullData());

        } catch (NotFoundHttpException $exception) {

            return $this->respondNotFoundError($exception->getMessage());
        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }

    /**
     * @Route("/{uuid}/confirm", methods={"PUT"})
     */
    public function confirmEarn(string $uuid,
                               EarnRepositoryInterface $earnRepository,
                               EarnServiceInterface $earnService)
    {
        try {
            $user = $this->getUser();

            $earn = $earnService->getEarnFromUserByIdOrFail($user->getId(), $uuid);
            $earn->earnConfirmed();
            $earn->updateLastUpdated();

            $earnRepository->save($earn);

            return $this->respondUpdatedResource();

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
                           EarnRepositoryInterface $earnRepository,
                           PlaceServiceInterface $placeService,
                           EarnServiceInterface $earnService,
                           DatetimeCheckServiceInterface $dateService)
    {
        try {
            $user = $this->getUser();
            $data = $request->request->all();

            $earn = $earnService->getEarnFromUserByIdOrFail($user->getId(), $uuid);
            $data = $placeService->getPlaceIfWasPassedOrFail($data, $user->getId());

            $datesAttribute = $earn->getAllAttributesDateAndFormat();
            $data = $dateService->getDatesListConvertedToDatetimeOrFail($datesAttribute,
                                                                        $data);
            $earn->setAttributes($data);
            $earn->updateLastUpdated();
            $validationService->entityIsValidOrFail($earn);
            $earnRepository->save($earn);

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
     * @Route("", methods={"GET"})
     */
    public function list(EarnServiceInterface $earnService)
    {
        try {
            $user = $this->getUser();
            $list = $earnService->getListAllByUser($user->getId());

            return $this->respondSuccess($list);

        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }

    /**
     * @Route("/{uuid}", methods={"DELETE"})
     */
    public function delete(string $uuid,
                           EarnServiceInterface $earnService,
                           EarnRepositoryInterface $earnRepository)
    {
        try {
            $user = $this->getUser();

            $earn = $earnService->getEarnFromUserByIdOrFail($user->getId(), $uuid);

            $earnRepository->remove($earn);

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