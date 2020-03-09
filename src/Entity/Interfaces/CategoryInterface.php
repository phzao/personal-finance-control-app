<?php

namespace App\Entity\Interfaces;

/**
 * @package App\Entity\Interfaces
 */
interface CategoryInterface
{
    public function setDefaultAndEnableIfIsDisable(): void;

    public function changeStatusAndSetNoDefaultIfNecessary(string $status);

    public function getCategoryInfo(): array;
}