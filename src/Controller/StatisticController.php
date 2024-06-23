<?php
declare(strict_types=1);

namespace App\Controller;

use App\Environment;
use App\Evaluator\MetricsExpertEvaluator;
use App\Model\StatisticFieldType;
use App\Parser\StatisticParser;
use App\Parser\RequestParser;

class StatisticController
{
    public function index(): void
    {
        require __DIR__ . '/../View/index_form.php';
    }

    public function showStatistic(array $requestData): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET')
        {
            return;
        }
        if (!$requestData['domain'])
        {
            throw new \RuntimeException('Url not passed');
        }
        $domain = $requestData['domain'];
        $jsonStatistic = file_get_contents(Environment::getStatisticPath($domain . '.json'));
        $evaluatedStatistic = json_decode($jsonStatistic, true, 512, JSON_THROW_ON_ERROR);
        require __DIR__ . '/../View/final_charts.php';
    }

    public function createStatistic(array $requestData): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST')
        {
            return;
        }

        if (!$requestData['url'])
        {
            throw new \RuntimeException('Url not passed');
        }
        $url = $requestData['url'];
        $stat = $this->parseStatistic($url);
        $evaluatedStatistic = $this->evaluateStatistic($stat);
        $domain = $this->saveJsonStatisticToFile($evaluatedStatistic, $url);
        if (empty($domain))
        {
            Environment::writeRedirectSeeOther('/index.php?error=1');
        }
        Environment::writeRedirectSeeOther("/show_results.php?domain=$domain");
    }

    private function evaluateStatistic(array $statistic): array
    {
        $evaluator = new MetricsExpertEvaluator();
        return $evaluator->evaluateExpertise($statistic);
    }

    private function parseStatistic(string $url): array
    {
        $result = [];
        $parser = new StatisticParser();
        $requestParser = new RequestParser();
        $pageCount = $requestParser->getPageCount($url);
        if ($pageCount == 0)
        {
            throw new \RuntimeException('Error in page request');
        }
        for ($pageNumber = 1; $pageNumber <= $pageCount; $pageNumber++)
        {
            $htmlData = $requestParser->getDataFromRequest($url, $pageNumber);
            if (!$htmlData)
            {
                throw new \RuntimeException('Failed to parse html');
            }
            $statistic = $parser->parseStatisticFromHtml($htmlData);
            $this->splitStatistic($statistic, $result);
        }

        return $result;
    }

    private function splitStatistic(array $statistic, array &$result): void
    {
        foreach ($statistic as $userName => $statisticValue)
        {
            if (!isset($result[$userName][StatisticFieldType::MESSAGES]))
            {
                $result[$userName][StatisticFieldType::MESSAGES] = $statisticValue[StatisticFieldType::MESSAGES];
                $result[$userName][StatisticFieldType::THEMES] = $statisticValue[StatisticFieldType::THEMES];
                $result[$userName][StatisticFieldType::LINE_BREAKS] = 0;
                $result[$userName][StatisticFieldType::SMILES] = 0;
                $result[$userName][StatisticFieldType::QUOTES] = 0;
                $result[$userName][StatisticFieldType::MESSAGES_IN_THEME] = 0;
            }
            $result[$userName][StatisticFieldType::LINE_BREAKS] += (int) $statisticValue[StatisticFieldType::LINE_BREAKS];
            $result[$userName][StatisticFieldType::SMILES] += (int) $statisticValue[StatisticFieldType::SMILES];
            $result[$userName][StatisticFieldType::QUOTES] += (int) $statisticValue[StatisticFieldType::QUOTES];
            ++$result[$userName][StatisticFieldType::MESSAGES_IN_THEME];
        }
    }

    private function saveJsonStatisticToFile(array $statistic, string $url): string
    {
        if (!$statistic)
        {
            return '';
        }
        $domain = self::getDomainDocumentFromUrl($url);
        $statFile = Environment::getStatisticPath($domain . '.json');
        file_put_contents($statFile, json_encode($statistic, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return $domain;
    }

    private static function getDomainDocumentFromUrl(string $url): string
    {
        return substr($url, strrpos($url, '/') + 1);
    }
}