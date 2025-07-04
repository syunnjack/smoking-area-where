@extends('layouts.plain')

@section('title', '喫煙所マップ')

@section('content')
<div class="container my-4">
  {{-- 見出しと導線 --}}
  <div class="text-center mb-4">
    <h2 class="fw-bold">🚬 喫煙所マップ</h2>
    <p class="text-muted">現在地から探す・空いている場所を見つける・誰でも投稿できる地図</p>
    <div class="d-flex justify-content-center gap-3 flex-wrap">
      <a href="{{ url('/spots?order=nearby') }}" class="btn btn-primary shadow-sm px-4">📍 近くの喫煙所</a>
      <a href="{{ url('/spots?order=popular') }}" class="btn btn-success shadow-sm px-4">🏆 人気順で見る</a>
      <a href="{{ url('/create') }}" class="btn btn-danger shadow-sm px-4">➕ 喫煙所を投稿</a>
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
        <option value="空いている" @selected(request('congestion')=='空いている')>空いている</option>
        <option value="やや混み" @selected(request('congestion')=='やや混み')>やや混み</option>
        <option value="混雑" @selected(request('congestion')=='混雑')>混雑</option>
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
      <h5 class="mb-3">📋 投稿スポット一覧</h5>
      <div class="overflow-auto" style="max-height: 460px;">
        @forelse($spots as $spot)
          <div class="card mb-3 border-0 shadow-sm">
            <div class="card-body">
              <h6 class="card-title d-flex justify-content-between mb-1">
                <a href="{{url("/spots/{$spot->id}")}}" class="text-decoration-none fw-semibold">
                {{ $spot->name }}
                <span class="badge bg-secondary">{{ $spot->area ?? '未設定' }}</span>
              </h6>
              <p class="mb-1 text-muted small">{{ $spot->description }}</p>
              <small class="text-muted">混雑度：{{ $spot->congestion ?? '未登録' }} ／ {{ $spot->created_at->diffForHumans() }}</small>
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
        $color = match($spot->congestion ?? '') {
          '混雑' => 'red',
          'やや混み' => 'orange',
          '空いている' => 'green',
          default => 'blue',
        };
      @endphp

      L.circleMarker([{{ $spot->lat }}, {{ $spot->lng }}], {
        radius: 8,
        color: '{{ $color }}',
        fillColor: '{{ $color }}',
        fillOpacity: 0.9
      }).addTo(map)
        .bindPopup(`<strong>{{ $spot->name }}</strong><br>{{ $spot->description }}<br><small>混雑度：{{ $spot->congestion ?? '未登録' }}</small>`);
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