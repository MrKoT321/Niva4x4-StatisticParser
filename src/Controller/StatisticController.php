<?php
declare(strict_types=1);

namespace App\Controller;

use App\Database\ConnectionProvider;
use App\Database\UserTable;
use App\Environment;
use App\Parser\StatisticParser;
use App\Parser\RequestParser;
use Ramsey\Uuid\Uuid;

class StatisticController
{
    public function index(): void
    {
        require __DIR__ . '/../View/index_form.php';
    }

    public function createStatistic(array $requestData): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST')
        {
            throw new \RuntimeException('Invalid request method');
        }

        $this->parseStatistic($requestData['url']);
        Environment::writeRedirectSeeOther('/index.php');
    }

    private function parseStatistic(string $url): void
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
        $this->saveJsonStatisticToFile($result, $url);
    }

    private function splitStatistic(array $statistic, array &$result): void
    {
        foreach ($statistic as $key => $statisticValue)
        {
            $userName = key($statisticValue);
            if (in_array($statisticValue[$userName]['stat'], $result, true))
            {
                continue;
            }
            if ($userName == 'Гость')
            {
                $result[$userName . ":" . Uuid::uuid4()->toString()] = $statisticValue[$userName]['stat'];
                continue;
            }
            $result[$userName] = $statisticValue[$userName]['stat'];
        }
    }

    private function saveJsonStatisticToFile(array $statistic, string $url): void
    {
        if (!$statistic)
        {
            return;
        }
        $statFile = Environment::getStatisticPath(self::getDomainDocumentFromUrl($url) . '.json');
        file_put_contents($statFile, json_encode($statistic, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private static function getDomainDocumentFromUrl(string $url): string
    {
        return substr($url, strrpos($url, '/') + 1);
    }
}