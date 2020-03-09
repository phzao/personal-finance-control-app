<?php

namespace App\Entity\Interfaces;

/**
 * Interface SearchInterface
 * @package App\Entity\Interfaces
 */
interface SearchInterface
{
    public function getParamsToListAll(array $params): array;
}