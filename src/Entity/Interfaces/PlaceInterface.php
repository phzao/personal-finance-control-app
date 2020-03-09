<?php

namespace App\Entity\Interfaces;

/**
 * @package App\Entity\Interfaces
 */
interface PlaceInterface
{
    public function setDefault(): void;

    public function getIdAndDescription(): array;
}