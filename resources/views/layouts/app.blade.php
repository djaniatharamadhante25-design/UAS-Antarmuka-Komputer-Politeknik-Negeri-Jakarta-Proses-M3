@php
    use App\Models\User;
    /** @var User|null $currentUser */
    $currentUser = auth()->user();
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Otaku Track{{ isset($title) ? ' — ' . $title : '' }}</title>
    <meta name="description" content="{{ $description ?? 'Otaku Track — katalog, watchlist, dan forum anime & manga.' }}">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    @vite(['resources/css/app.css'])
    @stack('styles')
</head>
<body>
<nav class="site-nav" id="siteHeader">
    <div class="container nav-inner">
        <!-- Brand on Left -->
        <div class="brand-col">
            <a class="brand" href="{{ route('home') }}" aria-label="Otaku Track">
                <span class="brand-mark">O</span>
                <span class="brand-name">Otaku<span class="brand-accent">Track</span></span>
            </a>
        </div>

        <!-- Center Floating Capsule (Navigation & Search) -->
        <div class="nav-center-col" id="primary-nav">
            <div class="nav-capsule">
                <div class="nav-links">
                    <a class="nav-item {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                        <i class="fa-solid fa-house"></i>
                        <span>Home</span>
                    </a>
                    <a class="nav-item {{ (request()->routeIs('search') && request('type') === 'anime') ? 'active' : '' }}" href="{{ route('search', ['type' => 'anime']) }}">
                        <i class="fa-solid fa-film"></i>
                        <span>Anime</span>
                    </a>
                    <a class="nav-item {{ (request()->routeIs('search') && request('type') === 'manga') ? 'active' : '' }}" href="{{ route('search', ['type' => 'manga']) }}">
                        <i class="fa-solid fa-book-open"></i>
                        <span>Manga</span>
                    </a>
                    <a class="nav-item {{ request()->routeIs('forum*') || request()->routeIs('thread*') ? 'active' : '' }}" href="{{ route('forum') }}">
                        <i class="fa-solid fa-comments"></i>
                        <span>Forum</span>
                    </a>
                </div>

                <div class="capsule-divider" aria-hidden="true"></div>

                <!-- Search Bar inside Center Capsule -->
                <form class="nav-search-capsule" method="get" action="{{ route('search') }}">
                    <input type="hidden" name="type" value="{{ request()->query('type', 'anime') === 'manga' ? 'manga' : 'anime' }}">
                    <i class="fa-solid fa-magnifying-glass search-icon" aria-hidden="true"></i>
                    <input name="q" value="{{ (string) request()->query('q', '') }}" placeholder="Search anime, manga..." aria-label="Cari anime atau manga">
                </form>

                <!-- Mobile Only User Actions (inside mobile drawer) -->
                <div class="mobile-user-area">
                    <div class="capsule-divider" aria-hidden="true"></div>
                    @if ($currentUser)
                        @if ($currentUser->role === 'admin')
                            <a class="admin-capsule-link" href="{{ route('admin') }}" title="Kelola data">
                                <i class="fa-solid fa-sliders"></i>
                                <span>Kelola Data</span>
                            </a>
                        @endif
                        <a class="user-capsule-pill" href="{{ route('profile') }}">
                            <span class="user-avatar-dot">{{ strtoupper(substr($currentUser->username, 0, 1)) }}</span>
                            <span class="user-name-label">{{ $currentUser->username }}</span>
                        </a>
                    @else
                        <a class="login-capsule-link" href="{{ route('login') }}">
                            <i class="fa-regular fa-user"></i>
                            <span>Login</span>
                        </a>
                        <a class="register-capsule-btn" href="{{ route('register') }}">
                            <span>Daftar</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right User Actions Capsule (Desktop) -->
        <div class="nav-right-col">
            <div class="nav-user-capsule">
                @if ($currentUser)
                    @if ($currentUser->role === 'admin')
                        <a class="admin-capsule-link" href="{{ route('admin') }}" title="Kelola data">
                            <i class="fa-solid fa-sliders"></i>
                        </a>
                    @endif
                    <a class="user-capsule-pill" href="{{ route('profile') }}" title="Profil {{ $currentUser->username }}">
                        <span class="user-avatar-dot">{{ strtoupper(substr($currentUser->username, 0, 1)) }}</span>
                        <span class="user-name-label">{{ $currentUser->username }}</span>
                    </a>
                @else
                    <a class="login-capsule-link" href="{{ route('login') }}">
                        <i class="fa-regular fa-user"></i>
                        <span>Login</span>
                    </a>
                    <a class="register-capsule-btn" href="{{ route('register') }}">
                        <span>Daftar</span>
                    </a>
                @endif
            </div>

            <!-- Mobile Menu Toggle Button -->
            <button class="menu-btn" id="nav-toggle" type="button" aria-label="Buka menu navigasi" aria-controls="primary-nav" aria-expanded="false">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>
    </div>
</nav>

<main>
    @if (session('success') || session('error'))
        <div class="container pt-3 flash-alerts">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
        </div>
    @endif
    @yield('content')
</main>

<footer class="site-footer">
    <div class="container">
        <div class="footer-top-row">
            <!-- Brand Info -->
            <div class="footer-brand">
                <a class="brand" href="{{ route('home') }}" aria-label="Otaku Track">
                    <span class="brand-mark">O</span>
                    <span class="brand-name">Otaku<span class="brand-accent">Track</span></span>
                </a>
                <p class="footer-tagline">Platform katalog, watchlist, dan komunitas penggemar anime & manga.</p>
            </div>

            <!-- Quick Navigation Links -->
            <div class="footer-nav-links">
                <a href="{{ route('home') }}">Home</a>
                <a href="{{ route('search', ['type' => 'anime']) }}">Anime</a>
                <a href="{{ route('search', ['type' => 'manga']) }}">Manga</a>
                <a href="{{ route('forum') }}">Forum</a>
                <a href="{{ route('search') }}">Pencarian</a>
            </div>
        </div>

        <div class="footer-divider" aria-hidden="true"></div>

        <!-- Disclaimer & Copyright -->
        <div class="footer-bottom-row">
            <p class="footer-disclaimer">
                <strong>Disclaimer:</strong> Otaku Track tidak mengunggah atau menyimpan berkas media apa pun di server kami. Seluruh metadata, gambar, dan informasi bersumber dari API publik pihak ketiga (AniList & Jikan/MyAnimeList).
            </p>
            <div class="footer-meta">
                <span>&copy; {{ date('Y') }} Otaku Track &middot; All rights reserved.</span>
                <span class="footer-badge">v5.0 &middot; AniList & Jikan API</span>
            </div>
        </div>
    </div>
</footer>

<!-- Floating Back to Top Button -->
<button id="backToTop" class="back-to-top" type="button" aria-label="Kembali ke atas">
    <i class="fa-solid fa-arrow-up"></i>
</button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@vite(['resources/js/app.js'])
@stack('scripts')
</body>
</html>
