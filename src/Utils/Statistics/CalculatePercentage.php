<?php

namespace App\Utils\Statistics;

/**
 * @package App\Utils\Statistics
 */
class CalculatePercentage implements CalculatePercentageInterface
{
    public function getPercentageFromList(array $list,
                                          string $colName = "value"): array
    {
        $total = 0;

        foreach ($list as $item)
        {
            $total += $item[$colName];
        }

        foreach ($list as $key => $item)
        {
            $percentage = ((float) $item[$colName] * 100)/$total;
            $list[$key]["percentage"] = round($percentage,2);
        }

        return $list;
    }

    public function getPercentageByMonthYear(array $list, string $colName = "value"): array
    {
        $totalByMonth = [];
        $totalGeneral = 0;

        foreach ($list as $item)
        {
            $description = $item["description"];
            $total = (float) $item["total"];
            $totalGeneral += $total;

            if(empty($totalByMonth[$description])){
                $totalByMonth[$description] = $total;
                continue;
            }

            $totalByMonth[$description] += $total;
        }

        $monthPercentage = [];

        foreach ($totalByMonth as $key=>$item)
        {
            $percentage = ((float) $item * 100)/$totalGeneral;

            $monthPercentage[] = [
                "description" => $key,
                "percentage" => round($percentage,2)
            ];
        }

        return $monthPercentage;
    }
}