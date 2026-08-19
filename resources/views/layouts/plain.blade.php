<!DOCTYPE html>
<html lang="ja">
<head>
  <meta name="google-site-verification" content="F_IVBMez9cL0M0SjGBQf-VepH0iKxalaQ8-LYH3tX8A" />
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#212529">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>@yield('title', config('app.name') . ' | みんなで探す・投稿する喫煙所マップ')</title>
  <meta name="description" content="@yield('description', '近くの喫煙所を地図から探せる投稿型マップです。混雑度・いいね・口コミをリアルタイムで確認でき、新しい喫煙所は誰でも匿名で投稿できます。')">
  @php
      // url()->current() はクエリを落とすため、絞り込み結果が一覧の正規URLを
      // 名乗ってしまう。内容が変わる条件だけを残す。
      $canonicalQuery = array_filter(
          request()->only(['area', 'congestion', 'page']),
          fn ($value) => $value !== null && $value !== '' && $value !== '1'
      );
      $canonicalUrl = url()->current() . ($canonicalQuery ? '?' . http_build_query($canonicalQuery) : '');
  @endphp
  <link rel="canonical" href="{{ $canonicalUrl }}">

  <meta property="og:site_name" content="{{ config('app.name') }}">
  <meta property="og:type" content="website">
  <meta property="og:title" content="@yield('title', config('app.name') . ' | みんなで探す・投稿する喫煙所マップ')">
  <meta property="og:description" content="@yield('description', '近くの喫煙所を地図から探せる投稿型マップです。混雑度・いいね・口コミをリアルタイムで確認でき、新しい喫煙所は誰でも匿名で投稿できます。')">
  <meta property="og:url" content="{{ $canonicalUrl }}">
  <meta property="og:locale" content="ja_JP">

  <meta name="twitter:card" content="summary">
  <meta name="twitter:title" content="@yield('title', config('app.name') . ' | みんなで探す・投稿する喫煙所マップ')">
  <meta name="twitter:description" content="@yield('description', '近くの喫煙所を地図から探せる投稿型マップです。混雑度・いいね・口コミをリアルタイムで確認でき、新しい喫煙所は誰でも匿名で投稿できます。')">

  <link rel="icon" href="/favicon.ico" sizes="any">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <style>
    body {
      background-color: #f8f9fa;
      font-family: system-ui, -apple-system, sans-serif;
    }
    /* スマホでのタップ操作を優先したサイズ調整 */
    .btn { min-height: 44px; }
    #map { touch-action: pan-x pan-y; }
    .btn-line { background: #06c755; color: #fff; border: none; }
    .btn-line:hover { background: #05a848; color: #fff; }
  </style>
  @yield('styles')

  @stack('structured-data')
  @if(config('services.ga4.id'))
  <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.ga4.id') }}"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '{{ config('services.ga4.id') }}');
  </script>
  @endif
</head>
<body>
  <nav class="navbar navbar-dark bg-dark p-2">
    <div class="container-fluid">
      <a href="{{ route('spots.index') }}" class="navbar-brand text-white text-decoration-none">🚬 {{ config('app.name') }}</a>
      <a href="{{ route('about') }}" class="text-white small text-decoration-none">サイトについて</a>
    </div>
  </nav>

  @yield('content')

  {{-- 掲載データは OpenStreetMap から取り込んでいる。ODbL 1.0 は出典表示を求めている。 --}}
  <footer class="border-top mt-5 py-4">
    <div class="container small text-muted">
      <p class="mb-1">
        掲載している喫煙所の情報の一部は
        <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a>
        の提供データ（&copy; OpenStreetMap contributors, ODbL 1.0）を利用しています。
      </p>
      <p class="mb-0">設置状況は変わることがあります。現地の掲示に従ってください。</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  @yield('scripts')
</body>
</html>
