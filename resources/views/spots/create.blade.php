@extends('layouts.plain')

@section('title', '喫煙所を投稿する - ' . config('app.name'))
@section('description', '地図をタップして場所を選び、喫煙所の名称とひとことコメントを投稿できます。ログイン不要・匿名で投稿可能です。')

@section('content')
<div class="container my-4">
  <h2 class="mb-3">➕ 喫煙所を投稿する</h2>
  <p class="text-muted small mb-3">地図をタップして場所を選択し、必要事項を入力してください。ログイン不要で投稿できます。</p>

  <div id="map" style="height: 360px;" class="rounded shadow-sm border mb-3"></div>

  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('spots.store') }}" class="bg-light p-3 rounded shadow-sm">
    @csrf

    {{-- ハニーポット: 人間には見えない項目。ボットが埋めた場合は投稿を無視する --}}
    <div style="position:absolute; left:-9999px;" aria-hidden="true">
      <label>ウェブサイト<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
    </div>

    <div class="mb-3">
      <label class="form-label">名称 <span class="text-danger">*</span></label>
      <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">ひとことコメント</label>
      <textarea name="description" rows="3" class="form-control">{{ old('description') }}</textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">エリア</label>
      <input type="text" name="area" value="{{ old('area') }}" class="form-control" placeholder="例：名古屋駅、栄">
    </div>

    <div class="row mb-3">
      <div class="col-6">
        <label class="form-label">緯度 <span class="text-danger">*</span></label>
        <input type="text" id="lat" name="lat" value="{{ old('lat') }}" class="form-control" readonly required>
      </div>
      <div class="col-6">
        <label class="form-label">経度 <span class="text-danger">*</span></label>
        <input type="text" id="lng" name="lng" value="{{ old('lng') }}" class="form-control" readonly required>
      </div>
    </div>

    <button type="submit" class="btn btn-danger w-100">登録する</button>
  </form>
</div>
@endsection

@section('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const map = L.map('map').setView([35.1709, 136.8815], 14);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    @foreach($spots as $spot)
      L.marker([{{ $spot->lat }}, {{ $spot->lng }}]).addTo(map)
        .bindPopup(`<strong>{{ $spot->name }}</strong>`);
    @endforeach

    let marker;
    map.on('click', function (e) {
      const lat = e.latlng.lat.toFixed(7);
      const lng = e.latlng.lng.toFixed(7);
      document.getElementById('lat').value = lat;
      document.getElementById('lng').value = lng;

      if (marker) {
        marker.setLatLng(e.latlng);
      } else {
        marker = L.marker(e.latlng).addTo(map);
      }
    });
  });
</script>
@endsection
