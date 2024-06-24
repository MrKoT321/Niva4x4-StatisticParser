<?php
declare(strict_types=1);

namespace App\Parser\StemmerPorter;

class StemmerPorterRussianLanguage
{
    private const VOWEL_REGEX = "/[аеёиоуыэюя]/iu";
    private const VOWEL_CONSONANT_REGEX = "/[аеёиоуыэюя][бвгджзйклмнпрстфхцчшщ]/iu";
    private const PERFECT_GERUND_REGEX = "/(([аяиы]вш?и?с?ь?)$)/iu";
    private const ADJECTIVAL_REGEX = "/((((аем)|(анн)|(авш)|(ающ)|(ащ)|(яем)|(янн)|(явш)|(яющ)|(ящ)|(ивш)|(ывш)|(ующ))|(([еиыо]е)|([иы]ми)|([еиыо]й)|([еиыо]м)|([ео]го)|([ео]му)|([иы]х)|([ыуое]ю)|([ая]я)))$)/iu";
    private const REFLEXIVE_REGEX = "/((ся$)|(сь$))/iu";
    private const VERB_REGEX = "/((([ая]((йте)|(ешь)|(ете)|(нно)|(ли)|(ем)|(ло)|(ть)|(ла)|(на)|(но)|(ет)|(ют)(ны)|(й)|(н)|(л)))|((уйте)|(ейте)|(ена)|(ыла)|(ила)|(ите)|(или)|(ыли)|(ишь)|(ило)|(ыло)|(ено)|(ыть)|(ует)|(уют)|(ены)|(ить)|(уй)|(ил)|(ыл)|(им)|(ым)|(ен)|(ит)|(ыт)|(ят)|(ей)|(ую)|(ю)))$)/iu";
    private const NOUN_REGEX = "/(((иями)|(ями)|(ами)|(иях)|(ией)|(иям)|(ием)|(ем)|(ам)|(ом)|(ах)|(ям)|(ях)|(еи)|(ии)|(ей)|(ой)|(ий)|(ию)|(ев)|(ов)|(ие)|(ье)|(ью)|(ия)|(ья)|(я)|(и)|(а)|(е)|(й)|(ы)|(ь)|(о)|(у)|(ю))$)/iu";
    private const SUPERLATIVE_REGEX = "/((ейш$)|(ейше$))/iu";
    private const DERIVATION_REGEX = "/((ост$)|(ость$))/iu";
    private const FIND_RV = 'rv';
    private const FIND_R2 = 'rv';

    public function getWordBase(string $word): string
    {
        $word = $this->transformWord($word);

        $word = $this->step1($word);
        $word = $this->step2($word);
        $word = $this->step3($word);
        return $this->step4($word);
    }

    private function transformWord(string $word): string
    {
        $word = mb_strtolower($word);
        $word = preg_replace('/[^а-яё]/iu', '', $word);

        return $word;
    }

    private function step1(string $word): string
    {
        if ($word != ($result = $this->cut($word, self::PERFECT_GERUND_REGEX)))
        {
            return $result;
        }

        if ($word != ($result = $this->cut($word, self::REFLEXIVE_REGEX)))
        {
            return $result;
        }

        if ($word != ($result = $this->cut($word, self::ADJECTIVAL_REGEX)))
        {
            return $result;
        }

        if ($word != ($result = $this->cut($word, self::VERB_REGEX)))
        {
            return $result;
        }

        if ($word != ($result = $this->cut($word, self::NOUN_REGEX)))
        {
            return $result;
        }
        return $word;
    }

    private function step2(string $word): string
    {
        return $this->cut($word, "/(и$)/iu");
    }

    private function step3(string $word): string
    {
        return $this->cut($word, self::DERIVATION_REGEX, self::FIND_R2);
    }

    private function step4(string $word): string
    {
        if ($word != ($result = $this->cut($word, "/(ь$)/iu")))
        {
            return $result;
        }
        if ($word != ($result = $this->cut($word, self::SUPERLATIVE_REGEX)))
        {
            $word = $result;
        }
        if ($word != ($result = $this->cut($word, "/(нн$)/iu")))
        {
            return  $result . 'н';
        }
        return $word;
    }

    private function findRv(string $word): int
    {
        preg_match(self::VOWEL_REGEX, $word, $matches, PREG_OFFSET_CAPTURE);

        if (!$matches)
        {
            return -1;
        }
        return (int) ($matches[0][1]) + 2;
    }
    
    private function findR2(string $word): int
    {
        preg_match(self::VOWEL_CONSONANT_REGEX, $word, $matches, PREG_OFFSET_CAPTURE);
        if (!$matches)
        {
            return -1;
        }
        $result = (int) $matches[0][1] + 4;
        preg_match(self::VOWEL_CONSONANT_REGEX, substr($word, $result), $matches, PREG_OFFSET_CAPTURE);
        if (!$matches)
        {
            return -1;
        }
        return $result + (int) ($matches[0][1]) + 4;
    }

    private function cut(string $word, string $regex, string $findSuffix = self::FIND_RV): string
    {
        $r = $findSuffix == self::FIND_RV ? $this->findRv($word) : $this->findR2($word);
        if ($r == -1)
        {
            return $word;
        }
        $rStr = substr($word, $r);

        preg_match($regex, $rStr, $matches, PREG_OFFSET_CAPTURE);
        if (!$matches)
        {
            return $word;
        }
        return substr($word, 0, $r) . substr($rStr, 0, strlen($rStr) - strlen($matches[0][0]));
    }
}