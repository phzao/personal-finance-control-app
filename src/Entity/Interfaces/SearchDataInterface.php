<?php

namespace App\Entity\Interfaces;

/**
 * @package App\Entity\Interfaces
 */
interface SearchDataInterface
{
    /**
     * Get data from a request and return only what's class needed.
     */
    public function getParamsToListAll(array $requestParams): array;
}