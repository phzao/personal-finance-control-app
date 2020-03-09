<?php

namespace App\Entity\Interfaces;

/**
 * @package App\Entity\Interfaces
 */
interface ReadUserOutsideInterface
{
    public function getNameAndIdUser($user): array;

    public function getIdUser($user): ?string;
}