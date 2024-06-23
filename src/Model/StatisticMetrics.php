<?php
declare(strict_types=1);

namespace App\Model;

class StatisticMetrics
{
    public const THEMES_METRIC = StatisticFieldType::THEMES;
    public const MESSAGES_METRIC = StatisticFieldType::MESSAGES;
    public const LINE_BREAKS_METRIC = StatisticFieldType::LINE_BREAKS;
    public const SMILES_METRIC = StatisticFieldType::SMILES;
    public const QUOTES_METRIC = StatisticFieldType::QUOTES;
    public const MESSAGES_IN_THEME_METRIC = StatisticFieldType::MESSAGES_IN_THEME;

    private const METRICS_COST = [
        self::THEMES_METRIC => 0.25,
        self::MESSAGES_METRIC => 0.5,
        self::LINE_BREAKS_METRIC => 0.75,
        self::SMILES_METRIC => 0.5,
        self::QUOTES_METRIC => 0.25,
        self::MESSAGES_IN_THEME_METRIC => 0.1,
    ];

    private const METRICS = [
        self::THEMES_METRIC,
        self::MESSAGES_METRIC,
        self::LINE_BREAKS_METRIC,
        self::SMILES_METRIC,
        self::QUOTES_METRIC,
        self::MESSAGES_IN_THEME_METRIC,
    ];

    private const MEAN_VALUE_METRICS = [
        self::LINE_BREAKS_METRIC,
        self::SMILES_METRIC,
        self::QUOTES_METRIC,
    ];

    public static function getMetricCost(): array
    {
        $metricCost = self::METRICS_COST;
        $costDiff = 1 / array_sum($metricCost);
        foreach ($metricCost as &$metric)
        {
            $metric *= $costDiff;
        }
        return $metricCost;
    }

    public static function getMetrics(): array
    {
        return self::METRICS;
    }

    public static function getMeanValueMetrics(): array
    {
        return self::MEAN_VALUE_METRICS;
    }
}