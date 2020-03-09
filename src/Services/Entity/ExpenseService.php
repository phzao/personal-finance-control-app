<?php

namespace App\Services\Entity;

use App\Entity\Expense;
use App\Repository\Interfaces\ExpenseRepositoryInterface;
use App\Repository\Interfaces\PlaceRepositoryInterface;
use App\Services\Entity\Interfaces\ExpenseServiceInterface;
use App\Services\Validation\ValidationService;
use App\Utils\Datetime\Interfaces\DatetimeCheckServiceInterface;
use App\Utils\Enums\GeneralTypes;
use App\Utils\Generators\TokenGeneratorInterface;
use App\Utils\HandleErrors\ErrorMessage;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @package App\Services\Entity
 */
final class ExpenseService implements ExpenseServiceInterface
{
    const FIRST_INSTALLMENT = 1;
    /**
     * @var PlaceRepositoryInterface
     */
    private $repository;

    /**
     * @var Expense 
     */
    private $expense;

    private $tokenGenerator;

    private $validator;

    private $datetimeChecker;

    /**
     * @throws \Exception
     */
    public function __construct(ExpenseRepositoryInterface $repository,
                                TokenGeneratorInterface $tokenGenerator,
                                DatetimeCheckServiceInterface $datetimeCheckService,
                                ValidationService $validationService)
    {
        $this->repository = $repository;
        $this->tokenGenerator = $tokenGenerator;
        $this->expense = new Expense();
        $this->validator = $validationService;
        $this->datetimeChecker = $datetimeCheckService;
    }

    public function getExpenseByIdOrFail(string $uuid): ? Expense
    {
        $expense = $this->repository->getByID($uuid);

        if (!$expense) {
            throw new NotFoundHttpException("There is no expense with this $uuid");
        }

        return $expense;
    }

    /**
     * @throws \Exception
     */
    public function getExpenseListToSave(array $params)
    {
        $maxInstallments = Expense::MIN_INSTALLMENT;
        $installmentToken = "";

        for($installmentTimes = 1; $installmentTimes <= $maxInstallments; $installmentTimes++)
        {
            $expense = new Expense();
            $expense->setAttributes($params);
            $expense->fixTotalInstallmentIfCash();

            if (!$expense->isToDivideThisExpense()) {
                yield $expense;
                break;
            };

            $expense->setValueDividedByEachInstallment();

            if ($installmentTimes===self::FIRST_INSTALLMENT) {
                $maxInstallments = $expense->getTotalTimesToDivideThisExpense();
                $expense->startMultipleInstallmentsOfThisExpense();
                $installmentToken = $expense->getGeneratedTokenInstallmentGroupUsing($this->tokenGenerator);
                yield $expense;
                continue;
            }

            $monthToAdd = $installmentTimes - 1;

            $newDueDate = $this->getDueDateInTheFutureIfExist($params,
                                                              "due_date",
                                                              $monthToAdd);
            $expense->setDueDate($newDueDate);

            $expense->setInstallmentNumber($installmentTimes);
            $expense->setTokenInstallmentGroup($installmentToken);

            yield $expense;
        }
    }

    private function getDueDateInTheFutureIfExist(array $params,
                                                  string $keyName,
                                                  int $installNumberFromTotalInstallments)
    {
        $newDueDate = null;

        if (!empty($params[$keyName])) {
            $num_month_ahead = $installNumberFromTotalInstallments;
            $newDueDate = $this->datetimeChecker
                                ->getMonthInTheFutureFrom($params[$keyName],
                                                          $num_month_ahead);
        }

        return $newDueDate;
    }

    public function getValidatedExpenseListToSaveOrFail(\Generator $expenseList)
    {
        foreach ($expenseList as $expense)
        {
            $this->validator->entityIsValidOrFail($expense);
            yield $expense;
        }
    }

    public function getSavedExpenseList(\Generator $generator): array
    {
        $list = [];
        foreach ($generator as $expense)
        {
            $this->repository->save($expense);
            $list[] = $expense->getFullData();
        }

        return $list;
    }

    public function getListAllBy(array $params, string $user_id): array
    {
        $params["registered_by"] = $user_id;

        return $this->repository->getAllBy($params);
    }

    public function getListNotDeletedBy(array $params, string $user_id): array
    {
        $params["registered_by"] = $user_id;
        $params["deleted_at"] = null;

        return $this->repository->getAllBy($params);
    }

    /**
     * @throws EntityNotFoundException
     */
    public function updateStatus(Expense $expense, string $status)
    {
        if (!$expense->getId()) {
            $msg  = ErrorMessage::getStringMessageToJson("expense", "Expense not defined!");

            throw new EntityNotFoundException($msg);
        }

        $expense->setAttribute('status', $status);

        $this->repository->save($expense);
    }

    public function isDescriptionIsTheSameFromTheLastRecordFail(string $user_id, array $request):void
    {
        $expense = $this->repository->getTheLastRecordByUser($user_id);
        if (!$expense) {
            return ;
        }

        $description = !empty($request["description"]) ?
                                $request["description"] : "";

        if (strtoupper($expense->getDescription()) === strtoupper($description)) {
            $msg = "Duplication detected!";
            $msg.= "You can't registered another expense with the same description from the last one.";
            throw new BadRequestHttpException($msg);
        }

        return;
    }

    public function getExpenseFromUserByIdAndNotDeletedOrFail(string $user_id, string $uuid): ? Expense
    {
        $params = [
            "registered_by" => $user_id,
            "id" => $uuid,
            "deleted_at" => null
        ];
        $expense = $this->repository->getOneBy($params);

        if (!$expense) {
            throw new NotFoundHttpException("There is no expense with this $uuid");
        }

        return $expense;
    }

    public function deleteOneExpenseOrAllGroupByToken(Expense $expense)
    {
        $expense->remove();

        if (!$expense->thisExpenseIsPartOfGroup()) {
            $this->repository->save($expense);
        } else {

            $deleted_date = $expense->getDeletedDateString();

            $this->repository->setAllExpensesDeletedAtByToken($expense->getTokenInstallmentGroup(), $deleted_date);
        }
    }

    public function isToChangePaymentType(array $expense, array $requestData): bool
    {
        if (empty($requestData["payment_type"])) {
            return false;
        }

        if ($expense['payment_type'] === $requestData['payment_type']) {
            return false;
        }

        return true;
    }

    public function isToChangeTotalInstallments(array $expense, array $requestData): bool
    {
        if (empty($requestData["total_installments"])) {
            return false;
        }

        if (empty($requestData["payment_type"]) && $expense["payment_type"]===GeneralTypes::STATUS_PAYMENT_CASH) {
            return false;
        }

        if ($expense['total_installments'] === $requestData['total_installments']) {
            return false;
        }

        return true;
    }

    public function getValueTotalByTokenGroup(string $token_group): ?float
    {
        $param = ["token_installment_group" => $token_group];
        $expenses = $this->repository->getAllBy($param);

        if (empty($expenses)) {
            return null;
        }

        $total = 0;

        foreach ($expenses as $expense)
        {
            $total+= $expense->getValue();
        }

        return $total;
    }

    /**
     * @throws \Exception
     */
    public function updateExpenseOrFail(Expense $expense, array $requestData)
    {
        $expenseData = $expense->getOriginalData();

        if (!$expense->isToDivideThisExpense() &&
            !$this->isToChangePaymentType($expenseData, $requestData) &&
            !$this->isToChangeTotalInstallments($expenseData, $requestData)) {

            $expense->setAttributes($requestData);
            $expense->fixTotalInstallmentIfCash();
            $this->validator->entityIsValidOrFail($expense);
            $this->repository->save($expense);

            return ;
        }

        if ($expense->isToDivideThisExpense()) {
            $tokenGroup = $expense->getTokenInstallmentGroup();
            $requestData["value"] = $this->getValueTotalByTokenGroup($tokenGroup);
        }

        $newExpenseData = array_merge($expenseData, $requestData);
        $expenseNew = new Expense();

        $expenseNew->setAttributes($newExpenseData);

        $this->validator->entityIsValidOrFail($expenseNew);

        $this->deleteOneExpenseOrAllGroupByToken($expense);

        $expensesList = $this->getExpenseListToSave($newExpenseData);

        $expensesListToSave = $this->getValidatedExpenseListToSaveOrFail($expensesList);
        $this->getSavedExpenseList($expensesListToSave);
    }

    public function getExpenseByUserOrFail(string $user_id, string $uuid): ?Expense
    {
        $expense = $this->repository->getOneByRegisteredAndID($user_id, $uuid);

        if (!$expense) {
            throw new NotFoundHttpException("There is no expense with this $uuid");
        }

        return $expense;
    }

    public function getFieldsAllowedToChange(array $params, array $fieldsNotAllowed): array
    {
        foreach ($params as $key => $field)
        {
            if (in_array($key, $fieldsNotAllowed)) {
                unset($params[$key]);
            }
        }

        return $params;
    }
}