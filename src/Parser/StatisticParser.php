<?php
declare(strict_types=1);

namespace App\Parser;

use App\Model\StatisticFieldType;

class StatisticParser
{
    public function parseStatisticFromHtml(string $htmlData): array
    {
        $result = [];
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($htmlData);
        $xpath = new \DOMXPath($doc);

        /* @var \DOMElement[] $usersPost */
        $usersPost = $xpath->evaluate('//tbody/tr');
        foreach ($usersPost as $post)
        {
            $userPost = $post->ownerDocument->saveHTML($post);
            if (!str_contains($userPost, '<td class="post_user">'))
            {
                continue;
            }
            $userStat = substr($userPost, strpos($userPost, '<td class="post_user">'));
            $userName = strip_tags(
                substr($userPost,
                    strpos($userPost, '<div>'),
                    strpos($userPost, '</div>') - strpos($userPost, '<div>')
                )
            );
            $parsedUserStat = $this->parseUserStatistic($userStat);
            $userPostText = substr($userPost, strpos($userPost, '<div class="post_text"'));
            $countBr = substr_count($userPost, '<br>');
            $countSmile = substr_count($userPost, '<img');
            $countQuote = substr_count($userPost, '<blockquote class="quote">');

            $result[$userName][StatisticFieldType::THEMES] = $parsedUserStat[StatisticFieldType::THEMES];
            $result[$userName][StatisticFieldType::MESSAGES] = $parsedUserStat[StatisticFieldType::MESSAGES];
            $result[$userName][StatisticFieldType::LINE_BREAKS] = $countBr;
            $result[$userName][StatisticFieldType::SMILES] = $countSmile;
            $result[$userName][StatisticFieldType::QUOTES] = $countQuote;
        }

        // распарсить блок по tr (скипать каждый второй tr, т.к. там просто полоса)
        // взять инфу клиент
        // взять инфу о сообщениях

        return $result;
    }

    private function parseUserStatistic(string $userStat): array
    {
        $result = [];
        preg_match('((\d+) / (\d+))', $userStat, $matches);
        if ($matches && $matches[1] && $matches[2])
        {
            $result[StatisticFieldType::THEMES] = (int) $matches[1];
            $result[StatisticFieldType::MESSAGES] = (int) $matches[2];
            return $result;
        }
        preg_match('(Сообщений: (\d+))', $userStat, $matches);
        $result[StatisticFieldType::THEMES] = null;
        $result[StatisticFieldType::MESSAGES] = $matches[1] ? (int) $matches[1] : null;
        return $result;
    }
}