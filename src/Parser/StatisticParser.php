<?php
declare(strict_types=1);

namespace App\Parser;

use App\Common\StatisticExtractor;
use App\Common\StatisticFieldType;

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
            $userName = strip_tags(
                substr($userPost,
                    strpos($userPost, '<div>'),
                    strpos($userPost, '</div>') - strpos($userPost, '<div>')
                )
            );
            $result[$userName] = $this->extractStatistic($userPost);
        }

        return $result;
    }

    private function extractStatistic(string $userPost): array
    {
        $extractor = new StatisticExtractor($userPost);
        return $extractor->extractStatistics();
    }
}