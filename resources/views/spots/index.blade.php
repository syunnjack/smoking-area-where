@extends('layouts.plain')

@section('title', config('app.name') . ' | 近くの喫煙所を地図から探す・投稿する')
@section('description', '全国の喫煙所を地図から検索できる投稿型マップです。エリア・混雑度で絞り込み、現在地から近い順や人気順で表示。新しい喫煙所は誰でも匿名で投稿できます。')

@push('structured-data')
<script type="application/ld+json">
{!! json_encode([
  '@context' => 'https://schema.org',
  '@type' => 'WebSite',
  'name' => config('app.name'),
  'url' => url('/'),
  'description' => '全国の喫煙所を地図から検索できる投稿型マップ。混雑度・いいね・口コミを確認できる。',
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
<script type="application/ld+json">
{!! json_encode([
  '@context' => 'https://schema.org',
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
                {{ $spot->name }}
                <span class="badge bg-secondary">{{ $spot->area ?? '未設定' }}</span>
              </h3>
              <p class="mb-1 text-muted small">{{ $spot->description }}</p>
              <small class="text-muted">混雑度：{{ \App\Helpers\CongestionHelper::getText($spot->average_congestion) }} ／ {{ $spot->created_at->diffForHumans() }}</small>
            </div>
          </div>
        @empty
          <p class="text-muted">該当するスポットがありません。</p>
        @endforelse
      </div>
    </div>
  </div>
</div>
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
