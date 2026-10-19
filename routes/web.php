<?php

use App\Http\Controllers\NewsController;
use App\Http\Controllers\AionRegistrationController;
use App\Http\Controllers\AionLoginController;
use App\Http\Controllers\AionPasswordController;
use App\Http\Controllers\AionCharacterController;
use App\Models\DonationOption;
use App\Models\DownloadItem;
use App\Models\News;
use App\Models\HeroSlide;
use App\Services\AionServerStatus;
use Illuminate\Support\Facades\Route;

Route::get('/', function (AionServerStatus $aionServerStatus) {
    $latestNews = News::query()
        ->where('status', 'published')
        ->where(function ($query) {
            $query->whereNull('published_at')
                ->orWhere('published_at', '<=', now());
        })
        ->orderByRaw('CASE WHEN published_at IS NULL THEN 1 ELSE 0 END')
        ->orderByDesc('published_at')
        ->orderByDesc('id')
        ->take(3)
        ->get();

    $heroSlides = HeroSlide::query()
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->orderBy('id')
        ->get();

    $serverMetrics = $aionServerStatus->snapshot();

    return view('home', compact('latestNews', 'heroSlides', 'serverMetrics'));
})->middleware(['website.available', 'feature.enabled:home'])->name('home');

Route::get('/news', [NewsController::class, 'index'])
    ->middleware(['website.available', 'feature.enabled:news'])
    ->name('news.index');

Route::get('/news/{slug}', [NewsController::class, 'show'])
    ->middleware(['website.available', 'feature.enabled:news'])
    ->name('news.show');

// Informational pages.
Route::get('/info', function (AionServerStatus $aionServerStatus) {
    $serverMetrics = $aionServerStatus->snapshot();

    return view('pages.info', compact('serverMetrics'));
})->middleware(['website.available', 'feature.enabled:info'])->name('info');
Route::get('/download', function () {
    $downloadItems = DownloadItem::query()
        ->where('is_active', true)
        ->where(function ($query) {
            $query->whereNull('published_at')
                ->orWhere('published_at', '<=', now());
        })
        ->orderBy('sort_order')
        ->orderBy('id')
        ->get();

    return view('pages.download', compact('downloadItems'));
})->middleware(['website.available', 'feature.enabled:download'])->name('download');
Route::get('/donation', function () {
    $donationOptions = DonationOption::query()
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->orderBy('id')
        ->get();

    return view('pages.donation', compact('donationOptions'));
})->middleware(['website.available', 'feature.enabled:donation'])->name('donation');
Route::get('/support', function () {
    $supportFaqs = \App\Models\SupportFaq::query()
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->orderBy('id')
        ->get();

    return view('pages.support', compact('supportFaqs'));
})->middleware(['website.available', 'feature.enabled:support'])->name('support');

// AION game account registration and session-based player login.
Route::get('/register', [AionRegistrationController::class, 'create'])
    ->middleware(['website.available', 'guest.aion', 'feature.enabled:account.registration'])
    ->name('register');
Route::post('/register', [AionRegistrationController::class, 'store'])
    ->middleware(['website.available', 'guest.aion', 'feature.enabled:account.registration', 'throttle:aion-register'])
    ->name('register.store');
Route::get('/login', [AionLoginController::class, 'create'])
    ->middleware(['website.available', 'guest.aion', 'feature.enabled:account.login'])
    ->name('login');
Route::post('/login', [AionLoginController::class, 'store'])
    ->middleware(['website.available', 'guest.aion', 'feature.enabled:account.login', 'throttle:aion-login'])
    ->name('login.store');
Route::post('/logout', [AionLoginController::class, 'destroy'])
    ->middleware('auth.aion')
    ->name('logout');
Route::get('/account', [AionLoginController::class, 'account'])
    ->middleware(['website.available', 'auth.aion'])
    ->name('account');
Route::get('/account/characters', [AionCharacterController::class, 'index'])
    ->middleware(['website.available', 'auth.aion', 'feature.enabled:account.characters'])
    ->name('account.characters');
Route::get('/account/change-password', [AionPasswordController::class, 'edit'])
    ->middleware(['website.available', 'auth.aion', 'feature.enabled:account.password'])
    ->name('account.password.edit');
Route::post('/account/change-password', [AionPasswordController::class, 'update'])
    ->middleware(['website.available', 'auth.aion', 'feature.enabled:account.password', 'throttle:aion-password-change'])
    ->name('account.password.update');

// Read-only JSON endpoint used by the public server status widgets.
Route::get('/api/server-status', function (AionServerStatus $aionServerStatus) {
    return response()->json($aionServerStatus->snapshot())
        ->header('Cache-Control', 'no-store, private')
        ->header('X-Content-Type-Options', 'nosniff');
})->middleware(['website.available', 'feature.enabled:server.status'])->name('api.server-status');

// Public XML sitemap for search engines.
Route::get('/sitemap.xml', function () {
    $urls = collect();

    if (\App\Models\ModulePlugin::featureEnabled('home')) {
        $urls->push(['loc' => route('home'), 'lastmod' => now()->toAtomString()]);
    }

    foreach ([
        'news' => 'news.index', 'info' => 'info', 'download' => 'download',
        'donation' => 'donation', 'support' => 'support',
    ] as $feature => $routeName) {
        if (\App\Models\ModulePlugin::featureEnabled($feature)) {
            $urls->push(['loc' => route($routeName), 'lastmod' => now()->toAtomString()]);
        }
    }

    if (\App\Models\ModulePlugin::featureEnabled('news')) News::query()
        ->where('status', 'published')
        ->where(function ($query) {
            $query->whereNull('published_at')->orWhere('published_at', '<=', now());
        })
        ->orderByDesc('published_at')
        ->get(['slug', 'updated_at'])
        ->each(function (News $news) use ($urls) {
            $urls->push(['loc' => route('news.show', $news->slug), 'lastmod' => $news->updated_at?->toAtomString()]);
        });

    return response(view('seo.sitemap', ['urls' => $urls])->render(), 200, [
        'Content-Type' => 'application/xml; charset=UTF-8',
    ]);
})->name('sitemap');

// Temporary signed preview for saved news articles.
Route::get('/news-preview/{news}', [NewsController::class, 'preview'])
    ->middleware('signed')
    ->name('news.preview');


// Only load route files for packages explicitly enabled in the registry. A disabled
// plugin's PHP route file is never required during application boot. If the registry
// cannot be queried (for example, before migrations), the core site still boots and
// plugin routes are omitted until the database is available again.
try {
    $pluginRoot = storage_path('app/aion-plugins');
    if (is_dir($pluginRoot) && \Illuminate\Support\Facades\Schema::hasTable('installed_plugins')) {
        $enabledPlugins = \App\Models\InstalledPlugin::query()
            ->where('is_enabled', true)
            ->get(['plugin_id', 'manifest'])
            ->keyBy('plugin_id');

        foreach (\Illuminate\Support\Facades\File::directories($pluginRoot) as $pluginDirectory) {
            $pluginId = basename($pluginDirectory);
            $registered = $enabledPlugins->get($pluginId);
            if (! $registered) continue;

            $routeFile = $pluginDirectory.'/routes/web.php';
            if (! is_file($routeFile)) continue;
            $manifest = is_array($registered->manifest) ? $registered->manifest : [];
            $manifestPath = $pluginDirectory.'/plugin.json';
            if (! is_file($manifestPath)) continue;
            $diskManifest = json_decode((string) file_get_contents($manifestPath), true);
            if (! is_array($diskManifest)
                || ($diskManifest['id'] ?? null) !== $pluginId
                || ($diskManifest['routes'] ?? null) !== 'routes/web.php'
                || ($manifest['id'] ?? null) !== $pluginId
                || ($manifest['routes'] ?? null) !== 'routes/web.php') continue;

            $basePath = trim((string) ($manifest['base_path'] ?? $pluginId), '/');
            if ($basePath !== '' && ! preg_match('#^[a-z0-9][a-z0-9/-]*$#', $basePath)) continue;

            $routeGroup = Route::middleware(['plugin.enabled:'.$pluginId])
                ->name('plugins.'.$pluginId.'.');
            if ($basePath !== '') $routeGroup->prefix($basePath);
            $routeGroup->group(function () use ($routeFile): void {
                require $routeFile;
            });
        }
    }
} catch (\Throwable $exception) {
    report($exception);
}

// Keep unknown URLs inside Laravel's web middleware so the session is available
// to the shared layout (including the AION account navbar) on custom 404 pages.
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
