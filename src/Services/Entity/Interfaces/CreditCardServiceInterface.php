<?php

namespace App\Services\Entity\Interfaces;

use App\Entity\CreditCard;
use App\Entity\User;

interface CreditCardServiceInterface
{
    public function getListAllByUser(string $user_id): array;

    public function getNotDeletedListByUser(string $user_id): array;

    public function getCreditCardFromUserByIdOrFail(string $user_id, string $uuid): ?CreditCard;

    public function updateStatus(CreditCard $creditCard, string $status);

    public function updateStatusDefaultSetting(CreditCard $category, string $uuid);

    public function logicDelete(CreditCard $creditCard): void;

    public function getOneAnywayIfExpenseIsOfTypeCreditCard(?User $user, array $request):?CreditCard;

    public function createOrLoadCreditCard(?User $user, array $request): array;
}