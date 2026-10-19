
<link rel="stylesheet" href="{{ asset('css/news-live-preview.css') }}">

<div class="news-live-preview">
    <div class="nlp-toolbar">
        <span>LIVE PREVIEW · AION PTS</span>
        <span class="nlp-status">UNPUBLISHED</span>
    </div>

    <main class="nlp-page">
        <section class="nlp-intro">
            <div class="nlp-eyebrow">News &amp; Announcements</div>
            <h2>Latest News</h2>
            <p>
                Updates, announcements, events, and news from the world of Atreia.
            </p>
        </section>

        <div class="news-detail-layout">
            <article class="news-detail-card">
                <header class="news-detail-header">
                    <div class="news-page-category">
                        {{ $category ?: 'Announcement' }}
                    </div>

                    <h1>{{ $title ?: 'Your news title appears here' }}</h1>

                    <div class="news-detail-meta">
                        Published {{ $publishedAt ?: 'Date not set' }}
                        &nbsp; · &nbsp; AION PTS Team

                        @if ($isFeatured ?? false)
                            &nbsp; · &nbsp; Featured Article
                        @endif
                    </div>
                </header>

                @if ($coverUrl)
                    <img
                        class="news-detail-cover"
                        src="{{ $coverUrl }}"
                        alt="{{ $title ?: 'News cover' }}"
                        loading="lazy"
                        decoding="async"
                    >
                @else
                    <div class="nlp-cover-placeholder">
                        AION PTS
                    </div>
                @endif

                <div class="news-detail-body">
                    @if ($excerpt)
                        <div class="news-detail-excerpt">
                            {{ $excerpt }}
                        </div>
                    @endif

                    <div class="news-detail-content">
                        <div class="article-content">
                            @if (filled($body))
                                {!! $body !!}
                            @else
                                <p>
                                    Your article content will appear here as you type.
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                <footer class="news-detail-footer">
                    <span class="nlp-back-button">
                        &larr; BACK TO NEWS
                    </span>
                </footer>
            </article>
        </div>

        <section class="nlp-seo">
            <div class="nlp-eyebrow">SEO Preview</div>

            <h3>{{ $metaTitle ?: $title ?: 'SEO title' }}</h3>

            <p>
                {{ $metaDescription ?: $excerpt ?: 'Your SEO description will appear here.' }}
            </p>
        </section>
    </main>
</div>
