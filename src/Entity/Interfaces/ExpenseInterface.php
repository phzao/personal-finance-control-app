<?php

namespace App\Entity\Interfaces;

use App\Entity\Category;
use App\Entity\CreditCard;
use App\Entity\Place;
use App\Entity\User;
use App\Utils\Generators\TokenGeneratorInterface;

/**
 * @package App\Entity\Interfaces
 */
interface ExpenseInterface
{
    public function isToDivideThisExpense():bool ;

    public function setRegisteredBy(?User $user): void;

    public function setCategory(Category $category): void;

    public function getDescription(): string;

    public function setPlace(Place $place): void;

    public function setCreditCard(CreditCard $creditCard): void;

    public function getTotalTimesToDivideThisExpense(): ?int;

    public function startMultipleInstallmentsOfThisExpense(): void;

    public function setValueDividedByEachInstallment():void;

    public function setInstallmentNumber(int $number): void;

    public function getPlaceIdDescription(): array;

    public function getPlaceId(): string;

    public function getRegisteredBy(): ? User;

    public function setDueDate(?\DateTimeInterface $dateTime);

    public function getGeneratedTokenInstallmentGroupUsing(TokenGeneratorInterface $tokenGenerator): string;

    public function setTokenInstallmentGroup(?string $token): void;

    public function thisExpenseIsPartOfGroup(): bool;

    public function getTokenInstallmentGroup(): string;

    public function getDeletedDateString(): string;

    public function getCreditCardDetails(): ? array;

    public function paidNowBy(?User $user): void;

    public function fixTotalInstallmentIfCash(): void;

    public function getValue(): float;

    public function getCategoryDetails(): ?array;

    public function getFieldsNotAllowedToChange(): ?array;
}