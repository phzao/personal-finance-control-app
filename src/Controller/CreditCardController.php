<?php

namespace App\Controller;

use App\Entity\CreditCard;
use App\Repository\Interfaces\CreditCardRepositoryInterface;
use App\Services\Entity\Interfaces\CreditCardServiceInterface;
use App\Services\Validation\ValidationService;
use App\Utils\Datetime\Interfaces\DatetimeCheckServiceInterface;
use App\Utils\Enums\GeneralTypes;
use Doctrine\DBAL\Types\ConversionException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @package App\Controller
 * @Route("/api/v1/credit-cards")
 */
class CreditCardController extends APIController
{
    /**
     * @Route("", methods={"POST"})
     */
    public function save(Request $request,
                         ValidationService $validationService,
                         CreditCardRepositoryInterface $repository,
                         DatetimeCheckServiceInterface $dateService)
    {
        try {
            $data  = $request->request->all();
            $creditCard = new CreditCard();
            $data["user"] = $this->getUser();

            $datesAttribute = $creditCard->getAllAttributesDateAndFormat();
            $data = $dateService->getDatesListConvertedToDatetimeOrFail($datesAttribute,
                                                                        $data);

            $creditCard->setAttributes($data);

            $validationService->entityIsValidOrFail($creditCard);
            $repository->save($creditCard);

            return $this->respondCreated($creditCard->getFullData());

        } catch (UnprocessableEntityHttpException $exception) {

            return $this->respondValidationFail($exception->getMessage());
        } catch (NotFoundHttpException $exception) {

            return $this->respondNotFoundError($exception->getMessage());
        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }

    /**
     * @Route("/{uuid}", methods={"GET"})
     */
    public function show(string $uuid, CreditCardServiceInterface $cardService)
    {
        try {
            $user = $this->getUser();

            $category = $cardService->getCreditCardFromUserByIdOrFail($user->getId(), $uuid);

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
                           CreditCardRepositoryInterface $cardRepository,
                           CreditCardServiceInterface $cardService,
                           DatetimeCheckServiceInterface $dateService)
    {
        try {
            $user = $this->getUser();
            $data = $request->request->all();

            $creditCard = $cardService->getCreditCardFromUserByIdOrFail($user->getId(), $uuid);

            $datesAttribute = $creditCard->getAllAttributesDateAndFormat();
            $data = $dateService->getDatesListConvertedToDatetimeOrFail($datesAttribute,
                                                                        $data);

            $creditCard->setAttributes($data);
            $validationService->entityIsValidOrFail($creditCard);
            $cardRepository->save($creditCard);

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
    public function list(CreditCardServiceInterface $creditCardService)
    {
        try {
            $user = $this->getUser();
            $list = $creditCardService->getNotDeletedListByUser($user->getId());

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
                                 CreditCardServiceInterface $cardService)
    {
        try {
            $user = $this->getUser();
            GeneralTypes::isValidDefaultStatusOrFail($status);

            $creditCard = $cardService->getCreditCardFromUserByIdOrFail($user->getId(), $uuid);
            $cardService->updateStatus($creditCard, $status);

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
                                        CreditCardServiceInterface $cardService)
    {
        try {
            $user = $this->getUser();

            $creditCard = $cardService->getCreditCardFromUserByIdOrFail($user->getId(), $uuid);
            $cardService->updateStatusDefaultSetting($creditCard, $user->getId());

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
                           CreditCardServiceInterface $cardService)
    {
        try {
            $user = $this->getUser();

            $creditCard = $cardService->getCreditCardFromUserByIdOrFail($user->getId(), $uuid);

            $cardService->logicDelete($creditCard);

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