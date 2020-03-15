<?php

namespace App\Utils\Statistics;

/**
 * @package App\Utils\Statistics
 */
class LineStatistic implements LineStatisticInterface
{
    public function getStatisticToNivoLineChart(array $list): array
    {
        $statistics = [];
        $totalByMonth = $this->getTotalByMonth($list);

        foreach ($list as $key => $item)
        {
            $description = $item["description"];

            if (!isset($statistics[$description])) {
                $statistics[$description] = [
                    "id" => $description,
                    "data" => []
                ];
            }

            $percentage = ((float) $item["total"] * 100)/$totalByMonth[$item["reference"]];

            $statistics[$description]["data"][] = [
                "x" => $item["reference"],
                "y" => round($percentage, 2),
                "total" => $item["total"]
            ];
        }

        sort($statistics);

        return $statistics;
    }

    private function getTotalByMonth(array $list): array
    {
        $totalByMonth = [];

        foreach ($list as $item)
        {
            if(empty($totalByMonth[$item["reference"]])){
                $totalByMonth[$item["reference"]] = (float) $item["total"];
            } else {
                $totalByMonth[$item["reference"]] += (float) $item["total"];
            }
        }

        return $totalByMonth;
    }
}