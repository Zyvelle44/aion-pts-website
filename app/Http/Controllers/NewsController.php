<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Support\NewsHtml;
use Illuminate\Contracts\View\View;

class NewsController extends Controller
{
    /**
     * Display published news articles.
     */
    public function index(): View
    {
        $news = News::query()
            ->where('status', 'published')
            ->where(function ($query) {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->orderByRaw(
                'CASE WHEN published_at IS NULL THEN 1 ELSE 0 END'
            )
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->paginate(9);

        return view('news.index', compact('news'));
    }

    /**
     * Display a published news article.
     */
    public function show(string $slug): View
    {
        $article = News::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->where(function ($query) {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->firstOrFail();

        $article->body = NewsHtml::sanitize($article->body);

        $sidebarNews = $this->getLatestNews($article->id);

        return view('news.show', compact(
            'article',
            'sidebarNews'
        ));
    }

    /**
     * Display a signed preview for authorized editorial users.
     */
    public function preview(News $news): View
    {
        abort_unless(
            auth()->check()
            && auth()->user()->hasAnyRole([
                'super_admin',
                'news_editor',
            ]),
            403
        );

        $news->body = NewsHtml::sanitize($news->body);

        $sidebarNews = $this->getLatestNews($news->id);

        return view('news.show', [
            'article' => $news,
            'sidebarNews' => $sidebarNews,
            'isPreview' => true,
        ]);
    }

    /**
     * Get the latest publicly available news for the sidebar.
     */
    private function getLatestNews(?int $excludeId = null)
    {
        return News::query()
            ->where('status', 'published')
            ->where(function ($query) {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->when(
                $excludeId !== null,
                fn ($query) => $query->where('id', '!=', $excludeId)
            )
            ->orderByRaw(
                'CASE WHEN published_at IS NULL THEN 1 ELSE 0 END'
            )
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->take(4)
            ->get();
    }
}
