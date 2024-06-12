<?php
declare(strict_types=1);

namespace App\Parser;

class StatisticParser
{
    public function parseStatisticFromHtml(string $htmlData): array
    {
        $result = [];
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($htmlData);
        $xpath = new \DOMXPath($doc);

        /* @var \DOMElement[] $users */
        $users = $xpath->evaluate('//td[@class="post_user"]');
        foreach ($users as $user)
        {
            $userName = $user->getElementsByTagName('div')->item(0)->firstChild->textContent;
            $parsedUserStat = $this->parseUserStatistic($user);
            $result[][$userName]['stat'] = $parsedUserStat;
        }

        return $result;
    }

    private function parseUserStatistic(\DOMElement $user): array
    {
        $userStat = $user->getElementsByTagName('p')->item(0)->textContent;
        preg_match('((\d+) / (\d+))', $userStat, $matches);
        $result['themes'] = $matches[1] ?? null;
        $result['messages'] = $matches[2] ?? null;
        return $result;
    }
}