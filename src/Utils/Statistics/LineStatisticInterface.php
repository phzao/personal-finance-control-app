<?php

namespace App\Utils\Statistics;

/**
 * @package App\Utils\Statistics
 */
interface LineStatisticInterface
{
    public function getStatisticToNivoLineChart(array $list): array;
}