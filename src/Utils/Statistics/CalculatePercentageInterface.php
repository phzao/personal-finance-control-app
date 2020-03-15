<?php

namespace App\Utils\Statistics;

/**
 * @package App\Utils\Statistics
 */
interface CalculatePercentageInterface
{
    public function getPercentageFromList(array $list, string $colName = "value"): array;

    public function getPercentageByMonthYear(array $list, string $colName = "value"): array;
}