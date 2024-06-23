<?php
declare(strict_types=1);

namespace App\Evaluator;

use App\Model\StatisticFieldType;
use App\Model\StatisticMetrics;

class MetricsExpertEvaluator
{
    private const EXPERT_NAME = 'ALER';
    private const SAMPLING_SIZE = 4;

    /**
     * Evaluate expertise of users by metrics
     *
     * @return array<string, float>
     */
    public function evaluateExpertise(array $statistic): array
    {
        $expertStat = $this->hydrateExpertStatistic($statistic);
        $evaluatedStatistic = $this->calculateExpertMetrics($statistic, $expertStat);
        arsort($evaluatedStatistic);
        return array_merge(
            array_slice($evaluatedStatistic, 0, self::SAMPLING_SIZE),
            array_slice($evaluatedStatistic, -self::SAMPLING_SIZE)
        );
    }

    /** @return array<string, float> */
    private function calculateExpertMetrics(array $statistic, array $expertStat): array
    {
        $metricCost = StatisticMetrics::getMetricCost();
        $result = [];

        $distanceByMetrics = $this->calculateDistanceToExpertByMetrics($statistic, $expertStat);
        foreach ($statistic as $userName => $stat)
        {
            $result[$userName] = 0;
            foreach ($metricCost as $metric => $cost)
            {
                $result[$userName] += $distanceByMetrics[$userName][$metric] * $cost;
            }
            $result[$userName] = round($result[$userName] * 100, 2);
        }

        return $result;
    }

    /** @return array<string, float> */
    private function calculateDistanceToExpertByMetrics(array $statistic, array $expertStat): array
    {
        $metrics = StatisticMetrics::getMetrics();
        $meanValueMetrics = StatisticMetrics::getMeanValueMetrics();
        $result = [];

        foreach ($statistic as $userName => $stat)
        {
            foreach ($metrics as $metric)
            {
                if (in_array($metric, $meanValueMetrics))
                {
                    $result[$userName][$metric] = self::calculateDistance(
                        (int) $expertStat[$metric] / (int) $expertStat[StatisticMetrics::MESSAGES_IN_THEME_METRIC],
                        (int) $stat[$metric] / (int) $expertStat[StatisticMetrics::MESSAGES_IN_THEME_METRIC]
                    );
                }
                else
                {
                    $result[$userName][$metric] = self::calculateDistance((int) $expertStat[$metric], (int) $stat[$metric]);
                }
            }
        }

        return $result;
    }

    private function findExpert(array $statistic): string
    {
        if (isset($statistic[self::EXPERT_NAME]))
        {
            return self::EXPERT_NAME;
        }

        $maxThemes = 0;
        $maxThemesUserName = '';
        foreach ($statistic as $username => $stat)
        {
            if ($stat[StatisticFieldType::THEMES] > $maxThemes)
            {
                $maxThemes = $stat[StatisticFieldType::THEMES];
                $maxThemesUserName = $username;
            }
        }

        return empty($maxThemesUserName)
            ? throw new \RuntimeException('No experts to be found')
            : $maxThemesUserName;
    }

    private function hydrateExpertStatistic(array &$statistic): array
    {
        $expertName = $this->findExpert($statistic);
        $expertStatistic = $statistic[$expertName];
        unset($statistic[$expertName]);

        return $expertStatistic;
    }

    private static function calculateDistance(float $value1, float $value2): float
    {
        $diff = abs($value1 - $value2);
        return $diff < $value1 ? $diff / $value1 : 0.0;
    }
}