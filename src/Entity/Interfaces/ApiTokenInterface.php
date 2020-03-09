<?php

namespace App\Entity\Interfaces;

use App\Entity\User;
use App\Utils\Generators\TokenGeneratorInterface;

/**
 * @package App\Entity\Interfaces
 */
interface ApiTokenInterface
{
    public function getUser(): ?User ;

    public function setUser(?User $user);

    public function invalidateToken(): void;

    public function getDetailsToken(): array;

    public function getId(): ?string;

    public function generateToken(TokenGeneratorInterface $tokenGenerator): void;

    public function getUserData(): array;

    //TODO implementar segurança
//    public function getTimeLastAccess():?\DateTimeInterface;
//
//    public function itIsLooksLikeDDOSByMinute(): bool;
//
//    public function zeroLastMinuteCount();
//
//    public function addCountAccess();
//
//    public function itIsLooksLikeDDOSByHour(): bool;
}