<?php

namespace App\Filament\Resources\News\Schemas;

use App\Support\NewsHtml;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Throwable;

class NewsLivePreview
{
    public static function data(Get $get): array
    {
        /*
         * Resolve cover image URL.
         */
        $cover = $get('cover_image');
        $coverUrl = null;

        if (is_array($cover)) {
            $cover = reset($cover) ?: null;
        }

        if ($cover instanceof TemporaryUploadedFile) {
            try {
                $coverUrl = $cover->temporaryUrl();
            } catch (Throwable) {
                $coverUrl = null;
            }
        } elseif (is_string($cover) && $cover !== '') {
            $disk = Storage::disk('public');

            if ($disk->exists($cover)) {
                $coverUrl = $disk->url($cover);
            }
        }

        /*
         * Resolve publication date.
         */
        $publishedAt = $get('published_at');

        /*
         * Prepare article body.
         */
        $body = (string) ($get('body') ?? '');

        // Convert legacy plain text into HTML paragraphs.
        if (trim($body) !== '' && ! str_contains($body, '<')) {
            $paragraphs = preg_split(
                '/\R\s*\R/u',
                trim($body)
            ) ?: [];

            $body = collect($paragraphs)
                ->filter(
                    fn ($paragraph) => trim($paragraph) !== ''
                )
                ->map(
                    fn ($paragraph) => '<p>'
                        . nl2br(e(trim($paragraph)), false)
                        . '</p>'
                )
                ->implode("\n");
        }

        // Sanitize HTML before rendering it in the live preview.
        $body = NewsHtml::sanitize($body);

        /*
         * Return live preview data.
         */
        return [
            'title' => (string) ($get('title') ?? ''),
            'category' => (string) (
                $get('category') ?? 'Announcement'
            ),
            'excerpt' => (string) ($get('excerpt') ?? ''),
            'body' => $body,
            'metaTitle' => (string) (
                $get('meta_title') ?? ''
            ),
            'metaDescription' => (string) (
                $get('meta_description') ?? ''
            ),
            'publishedAt' => $publishedAt
                ? (string) $publishedAt
                : null,
            'coverUrl' => $coverUrl,
            'isFeatured' => (bool) (
                $get('is_featured') ?? false
            ),
        ];
    }
}
