@props([
    'badge' => __('public.news.hero.badge'),
    'title',
    'subtitle' => null,
])

<header class="public-news-hero">
    <div class="public-news-hero-overlay">
        <div class="public-news-hero-content">
            <span class="public-news-hero-badge">{{ $badge }}</span>
            <h1>{{ $title }}</h1>
            @if($subtitle)
                <p>{{ $subtitle }}</p>
            @endif
            <div class="public-news-hero-divider"></div>
            {{ $slot }}
        </div>
    </div>
</header>
