<?php
declare(strict_types=1);

namespace App\Common;

use App\Parser\StemmerPorter\StemmerPorterRussianLanguage;

readonly class StatisticExtractor
{
    private string $userStat;

    public function __construct(
        private string $userPost
    ) {
        $this->userStat = substr($userPost, strpos($userPost, '<td class="post_user">'));
    }

    public function extractStatistics(): array
    {
        $stat = [];
        $userStat = $this->extractUserStatistic();

        $stat[StatisticFieldType::THEMES] = $userStat[StatisticFieldType::THEMES];
        $stat[StatisticFieldType::MESSAGES] = $userStat[StatisticFieldType::MESSAGES];
        $stat[StatisticFieldType::LINE_BREAKS] = $this->extractBrCount();
        $stat[StatisticFieldType::SMILES] = $this->extractSmileCount();
        $stat[StatisticFieldType::QUOTES] = $this->extractQuoteCount();
        $stat[StatisticFieldType::WORDS_BASE] = $this->extractUserPostSameWordsCount();

        return $stat;
    }

    private function extractBrCount(): int
    {
        return substr_count($this->userPost, '<br>');
    }

    private function extractSmileCount(): int
    {
        return substr_count($this->userPost, '<img');
    }

    private function extractQuoteCount(): int
    {
        return substr_count($this->userPost, '<blockquote class="quote">');
    }

    private function extractUserPostSameWordsCount(): array
    {
        $result = [];
        $userPostText = substr($this->userPost, strpos($this->userPost, '<div class="post_text"'));
        $userPostText = strip_tags($userPostText);
        $words = explode(' ', $userPostText);
        $wordBaseParser = new StemmerPorterRussianLanguage();

        foreach ($words as $word)
        {
            if (mb_strlen($word) < 4)
            {
                continue;
            }

            $wordBase = $wordBaseParser->getWordBase($word);
            if (isset($result[$wordBase]))
            {
                $result[$wordBase]++;
            }
            else
            {
                $result[$wordBase] = 1;
            }
        }

        return $result;
    }

    private function extractUserStatistic(): array
    {
        $result = [];
        preg_match('((\d+) / (\d+))', $this->userStat, $matches);
        if ($matches && $matches[1] && $matches[2])
        {
            $result[StatisticFieldType::THEMES] = (int) $matches[1];
            $result[StatisticFieldType::MESSAGES] = (int) $matches[2];
            return $result;
        }
        preg_match('(Сообщений: (\d+))', $this->userStat, $matches);
        $result[StatisticFieldType::THEMES] = null;
        $result[StatisticFieldType::MESSAGES] = $matches[1] ? (int) $matches[1] : null;
        return $result;
    }
}