<?php

namespace App\Controller;

use App\Entity\Expense;
use App\Repository\Interfaces\ExpenseRepositoryInterface;
use App\Services\Entity\Interfaces\CategoryServiceInterface;
use App\Services\Entity\Interfaces\CreditCardServiceInterface;
use App\Services\Entity\Interfaces\ExpenseServiceInterface;
use App\Services\Entity\Interfaces\PlaceServiceInterface;
use App\Utils\Datetime\Interfaces\DatetimeCheckServiceInterface;
use App\Utils\Statistics\CalculatePercentageInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @package App\Controller
 * @Route("/api/v1/expenses")
 */

class ExpenseController extends APIController
{
    /**
     * @Route("", methods={"POST"})
     */
    public function save(Request $request,
                         PlaceServiceInterface $placeService,
                         CategoryServiceInterface $categoryService,
                         ExpenseServiceInterface $expenseService,
                         CreditCardServiceInterface $cardService,
                         DatetimeCheckServiceInterface $dateService)
    {
        try {
            $data  = $request->request->all();
            $expense = new Expense();
            $user = $this->getUser();

            $data["registered_by"] = $user;
            $data["category"] = $categoryService->getOneByUserAnyway($user, $data);
            $data["place"] = $placeService->getOneByUserAnyway($user, $data);
            $data["creditCard"] = $cardService->getOneAnywayIfExpenseIsOfTypeCreditCard($user, $data);

            $datesAttribute = $expense->getAllAttributesDateAndFormat();
            $data = $dateService->getDatesListConvertedToDatetimeOrFail($datesAttribute,
                                                                        $data);

            $expenses = $expenseService->getExpenseListToSave($data);

            $expenses = $expenseService->getValidatedExpenseListToSaveOrFail($expenses);
            $expenseSaved = $expenseService->getSavedExpenseList($expenses);

            return $this->respondCreated($expenseSaved);
        } catch (BadRequestHttpException $exception) {

            return $this->respondNotAllowedError($exception->getMessage());
        } catch (UnprocessableEntityHttpException $exception) {

            return $this->respondValidationFail($exception->getMessage());
        }  catch (NotFoundHttpException $exception) {

            return $this->respondNotFoundError($exception->getMessage());
        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }

    /**
     * @Route("/batch", methods={"POST"})
     */
    public function batchSave(Request $request,
                              PlaceServiceInterface $placeService,
                              CategoryServiceInterface $categoryService,
                              ExpenseServiceInterface $expenseService,
                              CreditCardServiceInterface $cardService,
                              DatetimeCheckServiceInterface $dateService)
    {
        try {
            $expenseList  = $request->request->all();
            set_time_limit(0);
            $user = $this->getUser();

            $expenseSavedList = [];

            foreach($expenseList as $data)
            {
                $expense = new Expense();
                $data["registered_by"] = $user;
                $data["category"] = $categoryService->getOneByUserAnyway($user, $data);
                $data["place"] = $placeService->getOneByUserAnyway($user, $data);
                $data["creditCard"] = $cardService->getOneAnywayIfExpenseIsOfTypeCreditCard($user, $data);

                $datesAttribute = $expense->getAllAttributesDateAndFormat();
                $data = $dateService->getDatesListConvertedToDatetimeOrFail($datesAttribute, $data);

                $expenses = $expenseService->getExpenseListToSave($data);

                $listToSave = $expenseService->getValidatedExpenseListToSaveOrFail($expenses);
                $expenseSavedList = array_merge($expenseSavedList, $expenseService->getSavedExpenseList($listToSave));
            }

            return $this->respondCreated($expenseSavedList);
        } catch (BadRequestHttpException $exception) {

            return $this->respondNotAllowedError($exception->getMessage());
        } catch (UnprocessableEntityHttpException $exception) {

            return $this->respondValidationFail($exception->getMessage());
        }  catch (NotFoundHttpException $exception) {

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
                           PlaceServiceInterface $placeService,
                           CategoryServiceInterface $categoryService,
                           CreditCardServiceInterface $cardService,
                           DatetimeCheckServiceInterface $dateService,
                           ExpenseServiceInterface $expenseService)
    {
        try {
            $user = $this->getUser();
            $data  = $request->request->all();

            $expense = new Expense();

            $fieldsCantChange = $expense->getFieldsNotAllowedToChange();

            $data = $expenseService->getFieldsAllowedToChange($data, $fieldsCantChange);
            $datesAttr = $expense->getAllAttributesDateAndFormat();
            $data = $dateService->getDatesListConvertedToDatetimeOrFail($datesAttr, $data);

            $expense = $expenseService->getExpenseFromUserByIdAndNotDeletedOrFail($user->getId(), $uuid);

            $data = $categoryService->createOrLoadCategory($user, $data);
            $data = $placeService->createOrLoadPlace($user, $data);
            $data = $cardService->createOrLoadCreditCard($user, $data);

            $expenseService->updateExpenseOrFail($expense, $data);

            return $this->respondUpdatedResource();
        } catch (UnprocessableEntityHttpException $exception) {

            return $this->respondValidationFail($exception->getMessage());
        } catch (NotFoundHttpException $exception) {

            return $this->respondNotFoundError($exception->getMessage());
        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }

    /**
     * @Route("/paid/{uuid}", methods={"PUT"})
     */
    public function paid(string $uuid,
                         ExpenseRepositoryInterface $repository,
                         ExpenseServiceInterface $expenseService)
    {
        try {
            $user = $this->getUser();

            $expense = $expenseService->getExpenseFromUserByIdAndNotDeletedOrFail($user->getId(), $uuid);

            $expense->paidNowBy($user);
            $repository->save($expense);

            return $this->respondUpdatedResource();
        } catch (NotFoundHttpException $exception) {

            return $this->respondNotFoundError($exception->getMessage());
        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }

    /**
     * @Route("/{uuid}", methods={"DELETE"})
     */
    public function delete(string $uuid,
                           ExpenseServiceInterface $expenseService)
    {
        try {
            $user = $this->getUser();

            $expense = $expenseService->getExpenseFromUserByIdAndNotDeletedOrFail($user->getId(), $uuid);
            $expenseService->deleteOneExpenseOrAllGroupByToken($expense);

            return $this->respondUpdatedResource();
        } catch (NotFoundHttpException $exception) {

            return $this->respondNotFoundError($exception->getMessage());
        } catch (\Exception $exception) {
            return $this->respondBadRequestError($exception->getMessage());
        }
    }

    /**
     * @Route("", methods={"GET"})
     */
    public function list(Request $request, ExpenseServiceInterface $expenseService)
    {
        try {
            $data = $request->query->all();
            $user = $this->getUser();

            $list = $expenseService->getListNotDeletedAndOrderBy($data, $user->getId());

            return $this->respondSuccess($list);

        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }

    /**
     * @Route("/statistics/month", methods={"GET"})
     */
    public function pieStatistics(Request $request,
                                   ExpenseServiceInterface $expenseService,
                                   CalculatePercentageInterface $calculatePercentage)
    {
        try {
            $data = $request->query->all();
            $user = $this->getUser();

            $list = $expenseService->getExpensesToPieChartByDueDate($data, $user->getId());
            $statistic = $calculatePercentage->getPercentageFromList($list, "total");

            return $this->respondSuccess($statistic);

        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }

    /**
     * @Route("/statistics/year", methods={"GET"})
     */
    public function lineStatistics(ExpenseServiceInterface $expenseService,
                                   CalculatePercentageInterface $calculatePercentage)
    {
        try {
            $user = $this->getUser();

            $list = $expenseService->getExpensesToLineChartByLast12Months($user->getId());

            $statistic = $calculatePercentage->getPercentageByMonthYear($list);
            return $this->respondSuccess($statistic);

        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }

    /**
     * @Route("/{uuid}", methods={"GET"})
     */
    public function show(ExpenseServiceInterface $expenseService, string $uuid)
    {
        try {
            $user = $this->getUser();

            $expense = $expenseService->getExpenseFromUserByIdAndNotDeletedOrFail($user->getId(), $uuid);

            return $this->respondSuccess($expense->getFullData());

        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }
}