<?php

namespace App\Entity\Interfaces;

interface CreditCardInterface
{
    public function changeStatusAndSetNoDefaultIfNecessary(string $status): void;

    public function setDefaultAndEnableIfIsDisable(): void;

    public function getDetailsToExpense(): array;
}