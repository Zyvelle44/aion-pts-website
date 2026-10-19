@extends('layouts.app')

@section('title', 'Latest News | AION PTS — By Zyvalle')
@section('meta_description', 'Read the latest news, announcements, events, and updates from AION PTS 4.6 by Zyvalle.')

@section('content')
<main class="container">
    <section class="page-intro">
        <div class="eyebrow">News &amp; Announcements</div>
        <h1>Latest News</h1>
        <p>Updates, announcements, events, and news from the world of Atreia.</p>
    </section>

    <div class="news-page-grid">
        @forelse ($news as $article)
            <article class="news-page-card">
                <a href="{{ route('news.show', $article->slug) }}">
                    @if ($article->cover_image)
                        <img class="news-page-cover"
                             src="{{ asset('storage/' . $article->cover_image) }}"
                             alt="{{ $article->title }}"
                             loading="lazy">
                    @else
                        <div class="news-page-placeholder">AION</div>
                    @endif
                </a>

                <div class="news-page-card-content">
                    <div class="news-page-category">{{ $article->category ?: 'Announcement' }}</div>
                    <h2>
                        <a href="{{ route('news.show', $article->slug) }}">{{ $article->title }}</a>
                    </h2>
                    <div class="news-page-date">
                        {{ $article->published_at?->format('d M Y') ?: $article->created_at?->format('d M Y') }}
                    </div>
                    <p>
                        {{ $article->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($article->body), 140) }}
                    </p>
                    <a class="news-page-button" href="{{ route('news.show', $article->slug) }}">READ ARTICLE &rarr;</a>
                </div>
            </article>
        @empty
            <div class="news-page-empty">
                <div class="eyebrow">No News Available</div>
                <h2>No published articles yet.</h2>
                <p>News articles will appear here once they are published by the AION PTS team.</p>
                @if ($aionFeatures['home'] ?? false)<a class="news-page-button" href="{{ route('home') }}">BACK TO HOME</a>@endif
            </div>
        @endforelse
    </div>

    @if ($news->hasPages())
        <div class="news-page-pagination">{{ $news->links() }}</div>
    @endif
</main>
@endsection
