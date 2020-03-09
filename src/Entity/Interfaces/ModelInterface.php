<?php

namespace App\Entity\Interfaces;

/**
 * @package App\Entity\Interfaces
 */
interface ModelInterface
{
    public function setAttributes(array $values): void;

    public function setAttribute(string $key, $value): void;

    public function getFullData(): array;

    public function getOriginalData(): array;

    public function getId(): ?string;

    public function updateLastUpdated(): void;

    public function remove(): void;

    public function getDeletedAt(): ?\DateTime;
}