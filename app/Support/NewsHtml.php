<?php

namespace App\Support;

use Mews\Purifier\Facades\Purifier;

class NewsHtml
{
    public static function sanitize(?string $html): string
    {
        if (blank($html)) {
            return '';
        }

        return Purifier::clean($html, 'news_article') ?? '';
    }
}
