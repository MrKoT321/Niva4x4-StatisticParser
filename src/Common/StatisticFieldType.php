<?php
declare(strict_types=1);

namespace App\Common;

readonly class StatisticFieldType
{
    public const THEMES = 'themes';
    public const MESSAGES = 'messages';
    public const LINE_BREAKS = 'br_count';
    public const SMILES = 'smile_count';
    public const QUOTES = 'quote_count';
    public const MESSAGES_IN_THEME = 'messages_in_theme';
    public const WORDS_BASE = 'words_base';
}