<?php
declare(strict_types=1);

namespace App\Parser;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use MongoDB\BSON\Document;
use Psr\Http\Message\ResponseInterface;

class RequestParser
{
    private const URL_SEPARATOR = '/';
    private const FIRST_PAGE = 1;

    public function getDataFromRequest(string $url, int $pageNumber = 1): string
    {
        $result = '';
        $client = new Client();
        $urlToParse = $url . self::URL_SEPARATOR . $pageNumber;
        $request = new Request('GET', $urlToParse);
        $promise = $client->sendAsync($request)->then(function(ResponseInterface $response) use (&$result) {
            if ($response->getStatusCode() === 200)
            {
                $result = $response->getBody()->getContents();
            }
        });
        $promise->wait();
        return $result;
    }

    public function getPageCount(string $url): int
    {
        $client = new Client();
        $request = new Request('GET', $url . self::URL_SEPARATOR . self::FIRST_PAGE);
        $promise = $client->sendAsync($request)->then(function(ResponseInterface $response) use (&$result) {
            $doc = new \DOMDocument();
            $doc->loadHTML($response->getBody()->getContents());
            $xpath = new \DOMXPath($doc);
            if (!$this->isErrorPage($xpath))
            {
                /* @var \DOMElement[] $pageLinker */
                $pageLinker = $xpath->evaluate('//div//ul[@class="pages"]//li');
                $result = (int)$pageLinker[count($pageLinker) - 1]->textContent;
            }
        });
        $promise->wait();
        return $result ?? 0;
    }

    private function isErrorPage(\DOMXPath $xpath): bool
    {
        /* @var \DOMElement $errorPage */
        $errorPage = $xpath->evaluate('//div[@class="maincontent"]//h1');
        return (!$errorPage);
    }
}