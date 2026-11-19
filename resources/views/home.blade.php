@extends('layouts.app')

@php
    $poster = fn ($a) => App\Support\OtakuHelper::imageUrl($a['images']['jpg']['large_image_url'] ?? $a['images']['jpg']['image_url'] ?? null);
    $titleOf = fn ($a) => $a['title'] ?? $a['title_english'] ?? ($a['title_romaji'] ?? 'Untitled');

    // Kumpulkan seluruh data (popular, top, manga) untuk diacak (randomized billboard)
    $pool = array_merge($popular ?? [], $top ?? [], $manga ?? []);
    $uniquePool = [];
    $seen = [];
    foreach ($pool as $item) {
        $id = $item['mal_id'] ?? ($item['idMal'] ?? ($item['title'] ?? null));
        if ($id && !isset($seen[$id])) {
            $seen[$id] = true;
            $uniquePool[] = $item;
        }
    }
    shuffle($uniquePool);
    $slides = array_slice($uniquePool, 0, 5);
@endphp

@section('content')
<section class="hero-cinematic-slider">
  <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="6000">
    <!-- Carousel Indicators (Segmented Dash Bars at Bottom Right) -->
    <div class="carousel-indicators hero-dash-indicators">
      @foreach ($slides as $index => $s)
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="{{ $index }}" class="{{ $index === 0 ? 'active' : '' }}" aria-current="{{ $index === 0 ? 'true' : 'false' }}" aria-label="Slide {{ $index + 1 }}"></button>
      @endforeach
    </div>

    <!-- Carousel Slides -->
    <div class="carousel-inner">
      @foreach ($slides as $index => $s)
        @php
            $backdrop = $s['bannerImage'] ?? $s['images']['jpg']['large_image_url'] ?? $s['images']['jpg']['image_url'] ?? null;
            $slideType = isset($s['chapters']) ? 'manga' : 'anime';
            $score = $s['score'] ?? ($s['averageScore'] ? number_format($s['averageScore'] / 10, 1) : null);
            $year = $s['year'] ?? ($s['aired']['prop']['from']['year'] ?? ($s['seasonYear'] ?? null));
            $format = $s['type'] ?? ($s['format'] ?? (isset($s['chapters']) ? 'Manga' : 'TV Series'));
            
            $genreList = [];
            if (!empty($s['genres'])) {
                foreach (array_slice($s['genres'], 0, 3) as $g) {
                    $genreList[] = is_array($g) ? ($g['name'] ?? '') : $g;
                }
                $genreList = array_filter($genreList);
            }
            $detailUrl = App\Support\OtakuHelper::mediaLink($slideType, $s);

            // Badge dinamis bervariasi sesuai tipe & status item
            if ($slideType === 'manga') {
                $badgeIcon = 'fa-solid fa-book-open';
                $badgeText = 'FEATURED MANGA';
            } elseif ((float) ($score ?? 0) >= 8.5) {
                $badgeIcon = 'fa-solid fa-crown';
                $badgeText = 'TOP RATED PICK';
            } elseif (!empty($s['airing']) || (isset($s['status']) && strtolower((string) $s['status']) === 'currently airing')) {
                $badgeIcon = 'fa-solid fa-bolt';
                $badgeText = 'AIRING NOW';
            } else {
                $badgeIcon = 'fa-solid fa-fire';
                $badgeText = 'SPOTLIGHT PICK';
            }
        @endphp
        <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
          <!-- Cinematic Backdrop Image -->
          <div class="hero-slide-bg" style="background-image: url('{{ $backdrop ?: $poster($s) }}')"></div>
          
          <!-- Triple Layer Gradients for contrast -->
          <div class="hero-cinematic-overlay"></div>

          <!-- Hero Content -->
          <div class="container hero-content-container">
            <div class="hero-content-wrap">
              <!-- Top Badge -->
              <div class="hero-badge-row">
                <span class="hero-top-badge">
                  <i class="{{ $badgeIcon }} text-warning"></i>
                  <span>{{ $badgeText }}</span>
                </span>
                <span class="hero-tag-quality">HD</span>
                <span class="hero-tag-quality">SUB</span>
              </div>

              <!-- Main Title -->
              <h1 class="hero-cinematic-title">{{ $titleOf($s) }}</h1>

              <!-- Metadata Line -->
              <div class="hero-meta-row">
                @if ($score)
                  <span class="hero-meta-score">
                    <i class="fa-solid fa-star"></i> {{ $score }}
                  </span>
                @endif
                @if ($year)
                  <span class="hero-meta-item">{{ $year }}</span>
                @endif
                <span class="hero-meta-tag">{{ $format }}</span>
                @if (!empty($genreList))
                  <span class="hero-meta-genres">{{ implode(' · ', $genreList) }}</span>
                @endif
              </div>

              <!-- Synopsis Excerpt -->
              <p class="hero-synopsis">
                {{ App\Support\OtakuHelper::safeDescription($s['description'] ?? $s['synopsis'] ?? 'Temukan dan pantau anime & manga favoritmu langsung di Otaku Track.') }}
              </p>

              <!-- Dual CTA Action Buttons (Movy Style) -->
              <div class="hero-actions">
                <a class="btn-hero-primary" href="{{ $detailUrl }}">
                  <i class="fa-solid fa-play"></i>
                  <span>Detail & Watch</span>
                </a>
                <a class="btn-hero-secondary" href="{{ route('search', ['type' => $slideType]) }}">
                  <i class="fa-solid fa-circle-info"></i>
                  <span>Katalog {{ ucfirst($slideType) }}</span>
                </a>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>

    <!-- Arrow Controls -->
    <button class="carousel-control-prev hero-arrow-btn" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev" aria-label="Sebelumnya">
      <span class="hero-arrow-icon"><i class="fa-solid fa-chevron-left"></i></span>
    </button>
    <button class="carousel-control-next hero-arrow-btn" type="button" data-bs-target="#heroCarousel" data-bs-slide="next" aria-label="Berikutnya">
      <span class="hero-arrow-icon"><i class="fa-solid fa-chevron-right"></i></span>
    </button>
  </div>
</section>

<div class="container pb-5">
  {{-- 1. SECTION: TOP 10 ANIME --}}
  @if (!empty($top))
    <div class="movy-scroller-wrap">
      <div class="section-header-wrap">
        <div>
          <h2 class="section-cinematic-title">
            <span class="title-accent-bar red"></span>
            <span>TOP 10 Anime di Otaku Track</span>
          </h2>
          <p class="section-subtitle">Anime dengan skor & rating tertinggi pilihan komunitas</p>
        </div>
        <a class="section-view-all" href="{{ route('search', ['type' => 'anime']) }}">
          <span>Lihat Semua</span> <i class="fa-solid fa-chevron-right"></i>
        </a>
      </div>
      <div class="movy-scroller">
        @foreach (array_slice($top, 0, 10) as $rankIndex => $a)
          @php
            $score = $a['score'] ?? ($a['averageScore'] ? number_format($a['averageScore'] / 10, 1) : null);
            $year = $a['year'] ?? ($a['aired']['prop']['from']['year'] ?? ($a['seasonYear'] ?? null));
            $episodes = $a['episodes'] ?? null;
            $format = $a['type'] ?? ($a['format'] ?? 'Anime');
          @endphp
          <a class="movy-card" href="{{ App\Support\OtakuHelper::mediaLink('anime', $a) }}">
            <div class="movy-poster-box">
              <img src="{{ $poster($a) }}" 
                   alt="{{ $titleOf($a) }}" 
                   loading="lazy" 
                   onerror="this.onerror=null; this.src='https://placehold.co/300x450/111827/ffffff?text=Otaku+Track';">
              <span class="movy-rank-badge">#{{ $rankIndex + 1 }}</span>
              @if ($score)
                <span class="movy-rating-badge"><i class="fa-solid fa-star"></i> {{ $score }}</span>
              @endif
              <span class="movy-type-badge">{{ $format }}</span>
              <div class="movy-poster-overlay">
                <div class="movy-play-icon"><i class="fa-solid fa-play"></i></div>
              </div>
            </div>
            <div class="movy-card-info">
              <h4 class="movy-card-title" title="{{ $titleOf($a) }}">{{ $titleOf($a) }}</h4>
              <div class="movy-card-meta">
                <span>{{ $year ?: 'Anime' }}</span>
                @if ($episodes)
                  <span>&middot; {{ $episodes }} Ep</span>
                @endif
              </div>
            </div>
          </a>
        @endforeach
      </div>
    </div>
  @endif

  {{-- 2. SECTION: TRENDING ANIME --}}
  @if (!empty($popular))
    <div class="movy-scroller-wrap">
      <div class="section-header-wrap">
        <div>
          <h2 class="section-cinematic-title">
            <span class="title-accent-bar"></span>
            <span>Trending Anime Minggu Ini</span>
          </h2>
          <p class="section-subtitle">Paling banyak dibicarakan dan ditonton saat ini</p>
        </div>
        <a class="section-view-all" href="{{ route('search', ['type' => 'anime']) }}">
          <span>Lihat Semua</span> <i class="fa-solid fa-chevron-right"></i>
        </a>
      </div>
      <div class="movy-scroller">
        @foreach ($popular as $a)
          @php
            $score = $a['score'] ?? ($a['averageScore'] ? number_format($a['averageScore'] / 10, 1) : null);
            $year = $a['year'] ?? ($a['aired']['prop']['from']['year'] ?? ($a['seasonYear'] ?? null));
            $episodes = $a['episodes'] ?? null;
            $format = $a['type'] ?? ($a['format'] ?? 'Anime');
          @endphp
          <a class="movy-card" href="{{ App\Support\OtakuHelper::mediaLink('anime', $a) }}">
            <div class="movy-poster-box">
              <img src="{{ $poster($a) }}" 
                   alt="{{ $titleOf($a) }}" 
                   loading="lazy" 
                   onerror="this.onerror=null; this.src='https://placehold.co/300x450/111827/ffffff?text=Otaku+Track';">
              @if ($score)
                <span class="movy-rating-badge"><i class="fa-solid fa-star"></i> {{ $score }}</span>
              @endif
              <span class="movy-type-badge">{{ $format }}</span>
              <div class="movy-poster-overlay">
                <div class="movy-play-icon"><i class="fa-solid fa-play"></i></div>
              </div>
            </div>
            <div class="movy-card-info">
              <h4 class="movy-card-title" title="{{ $titleOf($a) }}">{{ $titleOf($a) }}</h4>
              <div class="movy-card-meta">
                <span>{{ $year ?: 'Anime' }}</span>
                @if ($episodes)
                  <span>&middot; {{ $episodes }} Ep</span>
                @endif
              </div>
            </div>
          </a>
        @endforeach
      </div>
    </div>
  @endif

  {{-- 3. SECTION: POPULAR MANGA --}}
  @if (!empty($manga))
    <div class="movy-scroller-wrap">
      <div class="section-header-wrap">
        <div>
          <h2 class="section-cinematic-title">
            <span class="title-accent-bar"></span>
            <span>Manga & Komik Terpopuler</span>
          </h2>
          <p class="section-subtitle">Koleksi manga terbaik untuk dibaca</p>
        </div>
        <a class="section-view-all" href="{{ route('search', ['type' => 'manga']) }}">
          <span>Lihat Semua</span> <i class="fa-solid fa-chevron-right"></i>
        </a>
      </div>
      <div class="movy-scroller">
        @foreach ($manga as $a)
          @php
            $score = $a['score'] ?? ($a['averageScore'] ? number_format($a['averageScore'] / 10, 1) : null);
            $chapters = $a['chapters'] ?? null;
            $format = $a['type'] ?? ($a['format'] ?? 'Manga');
          @endphp
          <a class="movy-card" href="{{ App\Support\OtakuHelper::mediaLink('manga', $a) }}">
            <div class="movy-poster-box">
              <img src="{{ $poster($a) }}" 
                   alt="{{ $titleOf($a) }}" 
                   loading="lazy" 
                   onerror="this.onerror=null; this.src='https://placehold.co/300x450/111827/ffffff?text=Otaku+Track';">
              @if ($score)
                <span class="movy-rating-badge"><i class="fa-solid fa-star"></i> {{ $score }}</span>
              @endif
              <span class="movy-type-badge">Manga</span>
              <div class="movy-poster-overlay">
                <div class="movy-play-icon"><i class="fa-solid fa-book-open"></i></div>
              </div>
            </div>
            <div class="movy-card-info">
              <h4 class="movy-card-title" title="{{ $titleOf($a) }}">{{ $titleOf($a) }}</h4>
              <div class="movy-card-meta">
                <span>Manga</span>
                @if ($chapters)
                  <span>&middot; {{ $chapters }} Ch</span>
                @endif
              </div>
            </div>
          </a>
        @endforeach
      </div>
    </div>
  @endif

  {{-- 4. SECTION: JELAJAHI GENRE POPULER --}}
  <div class="movy-scroller-wrap mt-3">
    <div class="section-header-wrap">
      <div>
        <h2 class="section-cinematic-title">
          <span class="title-accent-bar"></span>
          <span>Jelajahi Berdasarkan Genre</span>
        </h2>
        <p class="section-subtitle">Temukan serial dan bacaan sesuai genre favoritmu</p>
      </div>
    </div>
    <div class="genre-cards-grid">
      @php
        $genres = [
          ['name' => 'Action', 'icon' => 'fa-solid fa-fire'],
          ['name' => 'Romance', 'icon' => 'fa-solid fa-heart'],
          ['name' => 'Fantasy', 'icon' => 'fa-solid fa-wand-magic-sparkles'],
          ['name' => 'Shounen', 'icon' => 'fa-solid fa-bolt'],
          ['name' => 'Comedy', 'icon' => 'fa-solid fa-face-laugh-beam'],
          ['name' => 'Sci-Fi', 'icon' => 'fa-solid fa-robot'],
          ['name' => 'Supernatural', 'icon' => 'fa-solid fa-ghost'],
          ['name' => 'Adventure', 'icon' => 'fa-solid fa-compass'],
        ];
      @endphp
      @foreach ($genres as $g)
        <a class="genre-card" href="{{ route('search', ['q' => $g['name']]) }}">
          <i class="{{ $g['icon'] }}"></i>
          <span>{{ $g['name'] }}</span>
        </a>
      @endforeach
    </div>
  </div>
</div>
@endsection
