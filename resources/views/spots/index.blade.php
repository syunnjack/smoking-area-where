@extends('layouts.plain')

@php
    $pageTitle = $area
        ? $area . 'の喫煙所' . number_format($total) . 'か所｜' . config('app.name')
        : config('app.name') . ' | 近くの喫煙所を地図から探す・投稿する';
    $pageDescription = $area
        ? $area . 'の喫煙所' . number_format($total) . 'か所を市区町村別に地図と一覧で確認できます。混雑度と口コミは利用者の投稿です。'
        : '全国' . number_format($total) . 'か所の喫煙所を地図から探せます。都道府県から絞り込めるほか、混雑度の報告や新しい喫煙所の投稿も匿名でできます。';
@endphp

@section('title', $pageTitle)
@section('description', $pageDescription)

@push('structured-data')
<script type="application/ld+json">
{!! json_encode([
  '@@context' => 'https://schema.org',
  '@type' => 'WebSite',
  'name' => config('app.name'),
  'url' => url('/'),
  'description' => '全国の喫煙所を地図から検索できる投稿型マップ。混雑度・いいね・口コミを確認できる。',
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
{{-- 投稿が0件のときは itemListElement が空になる。空のItemListはGoogleに
     無効な項目として扱われるため、1件以上あるときだけ出力する。 --}}
@if ($spots->isNotEmpty())
<script type="application/ld+json">
{!! json_encode([
  '@@context' => 'https://schema.org',
  '@type' => 'ItemList',
  'itemListElement' => $spots->take(50)->values()->map(function ($spot, $i) {
      return [
          '@type' => 'ListItem',
          'position' => $i + 1,
          'url' => url("/spots/{$spot->id}"),
          'name' => $spot->name,
      ];
  })->all(),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endif
@endpush

@section('content')
<div class="container my-4">
  {{-- 見出しと導線 --}}
  <div class="text-center mb-4">
    <h1 class="fw-bold h3">🚬 喫煙所マップ</h1>
    <p class="text-muted">現在地から探す・空いている場所を見つける・誰でも投稿できる地図</p>
    <div class="d-flex justify-content-center gap-3 flex-wrap">
      <a href="{{ url('/spots?order=nearby') }}" class="btn btn-primary shadow-sm px-4">📍 近くの喫煙所</a>
      <a href="{{ url('/spots?order=popular') }}" class="btn btn-success shadow-sm px-4">🏆 人気順で見る</a>
      <a href="{{ route('spots.create') }}" class="btn btn-danger shadow-sm px-4">➕ 喫煙所を投稿</a>
    </div>
  </div>

  {{-- 絞り込みフォーム --}}
  <form method="GET" action="{{ url('/spots') }}" class="row g-2 mb-4">
    <div class="col-md-4">
      <label class="form-label">エリア</label>
      <select name="area" class="form-select">
        <option value="">すべて</option>
        @foreach($areas as $area)
          <option value="{{ $area }}" @selected(request('area') == $area)>{{ $area }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">混雑度</label>
      <select name="congestion" class="form-select">
        <option value="">すべて</option>
        <option value="empty" @selected(request('congestion')=='empty')>空いている</option>
        <option value="slightly_crowded" @selected(request('congestion')=='slightly_crowded')>やや混雑</option>
        <option value="crowded" @selected(request('congestion')=='crowded')>混雑</option>
        <option value="very_crowded" @selected(request('congestion')=='very_crowded')>非常に混雑</option>
      </select>
    </div>
    <div class="col-md-2 align-self-end">
      <button type="submit" class="btn btn-outline-primary w-100">絞り込む</button>
    </div>
  </form>

  @if($area)
    <nav aria-label="パンくず" class="small mb-2">
      <a href="{{ route('spots.index') }}">喫煙所どこ</a>
      <span class="text-muted mx-1">/</span><span class="text-muted">{{ $area }}</span>
    </nav>
  @endif

  @if($areaCounts->isNotEmpty())
    <h2 class="h6">都道府県から探す</h2>
    <p class="d-flex flex-wrap gap-2 mb-4">
      @foreach($areaCounts as $row)
        <a href="{{ route('spots.area', $row['slug']) }}"
           class="btn btn-sm {{ $areaSlug === $row['slug'] ? 'btn-primary' : 'btn-outline-secondary' }}">
          {{ $row['area'] }} <span class="text-muted">{{ number_format($row['total']) }}</span>
        </a>
      @endforeach
    </p>
  @endif

  {{-- 地図と投稿一覧 --}}
  <div class="row">
    <div class="col-lg-7 mb-4">
      <div id="map" style="height: 460px;" class="rounded shadow-sm border"></div>
    </div>
    <div class="col-lg-5">
      <h2 class="h5 mb-3">📋 投稿スポット一覧</h2>
      <div class="overflow-auto" style="max-height: 460px;">
        @forelse($spots as $spot)
          <div class="card mb-3 border-0 shadow-sm">
            <div class="card-body">
              <h3 class="card-title h6 d-flex justify-content-between mb-1">
                <a href="{{url("/spots/{$spot->id}")}}" class="text-decoration-none fw-semibold">
                {{ $spot->display_name }}
                @if($spot->area_slug)
                  <a href="{{ route('spots.area', $spot->area_slug) }}" class="badge bg-secondary text-decoration-none">{{ $spot->area }}</a>
                @else
                  <span class="badge bg-secondary">未設定</span>
                @endif
              </h3>
              <p class="mb-1 text-muted small">{{ $spot->description }}</p>
              <small class="text-muted">混雑度：{{ \App\Helpers\CongestionHelper::getText($spot->average_congestion) }} ／ {{ $spot->created_at->diffForHumans() }}</small>
            </div>
          </div>
        @empty
          <p class="text-muted">該当するスポットがありません。</p>
        @endforelse
      </div>

      @if($spots instanceof \Illuminate\Contracts\Pagination\Paginator)
        <div class="d-flex justify-content-center mt-3">
          {{ $spots->onEachSide(1)->links() }}
        </div>
      @endif
    </div>
  </div>
</div>
<p class="container text-muted small mt-4">
  喫煙所の位置は OpenStreetMap のデータをもとにしています（© OpenStreetMap contributors、ODbL 1.0）。
  市区町村名は座標から国土地理院の地図データで補っています。
  混雑度と口コミは利用者の投稿で、当サイトでは内容を確認していません。
  喫煙の可否や利用時間は現地の表示に従ってください。
</p>
@endsection

@section('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    // 地図の初期化
    const map = L.map('map').setView([35.1709, 136.8815], 14);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // マーカー描画（Blade ループ）
    @foreach($spots as $spot)
      @php
        $color = match(true) {
          $spot->average_congestion === null => 'blue',
          $spot->average_congestion >= 2.5 => 'red',
          $spot->average_congestion >= 1.5 => 'orange',
          default => 'green',
        };
      @endphp

      L.circleMarker([{{ $spot->lat }}, {{ $spot->lng }}], {
        radius: 8,
        color: '{{ $color }}',
        fillColor: '{{ $color }}',
        fillOpacity: 0.9
      }).addTo(map)
        .bindPopup(`<strong>{{ $spot->name }}</strong><br>{{ $spot->description }}<br><small>混雑度：{{ \App\Helpers\CongestionHelper::getText($spot->average_congestion) }}</small>`);
    @endforeach

    // 📍現在地ボタン機能
    const nearbyBtn = document.querySelector('a[href*="order=nearby"]');
    if (nearbyBtn && navigator.geolocation) {
      nearbyBtn.addEventListener('click', function (e) {
        e.preventDefault();
        navigator.geolocation.getCurrentPosition(function (pos) {
          const lat = pos.coords.latitude;
          const lng = pos.coords.longitude;
          const url = `/spots?order=nearby&lat=${lat}&lng=${lng}`;
          window.location.href = url;
        }, function () {
          alert('位置情報の取得に失敗しました。');
        });
      });
    }
  });
</script>
@endsection
