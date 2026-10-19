<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GeminiNewsWriter
{
    public function generate(string $topic, string $category): array
    {
        $apiKey = config('services.gemini.api_key');
        $model = config('services.gemini.model', 'gemini-3.5-flash-lite');

        if (blank($apiKey)) {
            throw new RuntimeException(
                'Gemini API key is missing. Configure GEMINI_API_KEY in .env.'
            );
        }

        $prompt = <<<PROMPT
You are the official English-language news writer for AION PTS 4.6 by Zyvalle.

Create an editable news article draft based on the administrator's input.

TOPIC:
{$topic}

CATEGORY:
{$category}

REQUIREMENTS:
- Write all content in natural, professional English.
- Use a clear MMORPG community news style.
- Do not invent dates, events, maintenance schedules, rewards, server statistics, technical changes, or official announcements that were not provided.
- If information is missing, keep the wording general rather than inventing facts.
- Write the article body as plain text with a blank line between paragraphs.
- Use descriptive section headings where appropriate.
- Do not include Markdown symbols such as **, ##, or backticks in the body.
- Do not publish the article. Generate a draft for administrator review only.
- Create a concise, SEO-friendly title and excerpt.
- Generate a URL-friendly lowercase slug using hyphens.
- SEO title must be at most 70 characters.
- SEO description must be at most 170 characters.
- Return only a valid JSON object with these exact keys:
  title, slug, category, excerpt, body, meta_title, meta_description.
PROMPT;

        $response = Http::connectTimeout(10)
            ->timeout(60)
            ->withHeaders([
                'x-goog-api-key' => $apiKey,
            ])
            ->acceptJson()
            ->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent",
                [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 3000,
                        'responseMimeType' => 'application/json',
                    ],
                ]
            );

        if ($response->failed()) {
            Log::warning('Gemini news generation request failed.', [
                'status' => $response->status(),
            ]);

            throw new RuntimeException(
                'Gemini could not generate the draft. Check the API key, model, quota, and API access.'
            );
        }

        $text = $response->json('candidates.0.content.parts.0.text');

        if (! is_string($text) || trim($text) === '') {
            throw new RuntimeException(
                'Gemini returned an empty response. Please try again.'
            );
        }

        $draft = json_decode($text, true);

        if (! is_array($draft) || json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException(
                'Gemini returned invalid JSON. Please generate the draft again.'
            );
        }

        $requiredFields = [
            'title',
            'slug',
            'category',
            'excerpt',
            'body',
            'meta_title',
            'meta_description',
        ];

        foreach ($requiredFields as $field) {
            if (! isset($draft[$field]) || ! is_string($draft[$field])) {
                throw new RuntimeException(
                    "Gemini did not return a valid {$field} field."
                );
            }
        }

        $draft['title'] = trim($draft['title']);
        $draft['slug'] = str($draft['slug'])->slug()->toString();
        $draft['category'] = $category;
        $draft['excerpt'] = trim($draft['excerpt']);

        // Convert plain text from Gemini into formatted HTML.
        $draft['body'] = $this->articleTextToHtml($draft['body']);

        $draft['meta_title'] = trim($draft['meta_title']);
        $draft['meta_description'] = trim($draft['meta_description']);

        if (
            $draft['title'] === '' ||
            $draft['slug'] === '' ||
            trim(strip_tags($draft['body'])) === ''
        ) {
            throw new RuntimeException(
                'The generated draft is missing essential content.'
            );
        }

        $draft['title'] = mb_substr($draft['title'], 0, 200);
        $draft['slug'] = mb_substr($draft['slug'], 0, 220);
        $draft['excerpt'] = mb_substr($draft['excerpt'], 0, 500);
        $draft['meta_title'] = mb_substr($draft['meta_title'], 0, 70);
        $draft['meta_description'] = mb_substr(
            $draft['meta_description'],
            0,
            170
        );

        return $draft;
    }

    /**
     * Convert plain text into HTML paragraphs and headings.
     */
    private function articleTextToHtml(string $text): string
    {
        $text = trim(str_replace(["\r\n", "\r"], "\n", $text));

        if ($text === '') {
            return '';
        }

        // Split the article into blocks separated by blank lines.
        $blocks = preg_split('/\n[ \t]*\n+/u', $text);

        $html = [];
        $listItems = [];

        $flushList = function () use (&$html, &$listItems): void {
            if ($listItems === []) {
                return;
            }

            $html[] = '<ul>'
                . implode('', $listItems)
                . '</ul>';

            $listItems = [];
        };

        foreach ($blocks as $block) {
            $block = trim($block);

            if ($block === '') {
                continue;
            }

            // Support headings if Gemini returns Markdown headings.
            if (preg_match('/^#{1,3}\s+(.+)$/u', $block, $matches)) {
                $flushList();

                $level = min(
                    strlen(strstr($block . ' ', ' ', true) ?: '#'),
                    3
                );

                // Determine heading level from the leading # characters.
                preg_match('/^(#{1,3})\s+/', $block, $headingMatch);
                $level = strlen($headingMatch[1] ?? '#');

                $heading = trim($matches[1]);

                $html[] = '<h' . $level . '>'
                    . e($heading)
                    . '</h' . $level . '>';

                continue;
            }

            // Support simple bullet lists.
            $lines = explode("\n", $block);
            $isList = true;
            $items = [];

            foreach ($lines as $line) {
                if (preg_match('/^\s*[-*]\s+(.+)$/u', $line, $matches)) {
                    $items[] = '<li>' . e(trim($matches[1])) . '</li>';
                } else {
                    $isList = false;
                    break;
                }
            }

            if ($isList && $items !== []) {
                $flushList();
                $html[] = '<ul>' . implode('', $items) . '</ul>';
                continue;
            }

            $flushList();

            // Escape text and preserve line breaks within paragraphs.
            $html[] = '<p>'
                . nl2br(e($block), false)
                . '</p>';
        }

        $flushList();

        return implode("\n", $html);
    }
}
