<?php

namespace App\Entity\Interfaces;

use App\Entity\Place;

interface EarnInterface
{
    public function getPlaceId(): ?string;

    public function getPlaceIDAndDescription(): ?array;

    public function setPlace(Place $place);

    public function getPlace(): ? Place;

    public function earnConfirmed();
}