<?php

namespace App\Services\Entity\Interfaces;

use App\Entity\Expense;

/**
 * @package App\Services\Entity\Interfaces
 */
interface ExpenseServiceInterface
{
    public function getExpenseByIdOrFail(string $uuid);

    public function isDescriptionIsTheSameFromTheLastRecordFail(string $user_id, array $request): void;

    public function getListAllBy(array $params, string $user_id): array;

    public function getExpenseListToSave(array $params);

    public function getValidatedExpenseListToSaveOrFail(\Generator $generator);

    public function getSavedExpenseList(\Generator $generator): array;

    public function updateStatus(Expense $place, string $status);

    public function getExpenseFromUserByIdAndNotDeletedOrFail(string $user_id, string $uuid): ?Expense;

    public function deleteOneExpenseOrAllGroupByToken(Expense $expense);

    public function getListNotDeletedBy(array $params, string $user_id): array;

    public function updateExpenseOrFail(Expense $expense, array $requestData);

    public function getExpenseByUserOrFail(string $user_id, string $uuid):?Expense;

    public function getFieldsAllowedToChange(array $params, array $fieldsNotAllowed): array;

    public function getValueTotalByTokenGroup(string $token_group): ?float;

    public function isToChangeTotalInstallments(array $expense, array $requestData): bool;

    public function isToChangePaymentType(array $expense, array $requestData): bool;
}