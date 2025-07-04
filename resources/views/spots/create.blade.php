<!DOCTYPE html>
<html>
<head>
    <title>喫煙所を追加</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        #map { height: 400px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <h2>喫煙所を追加</h2>

    <div id="map"></div>

    <form method="POST" action="{{ url('/store') }}">
        @csrf
        <label>名称：<input type="text" name="name" required></label><br>
        <label>ひとことコメント：<textarea name="description" required></textarea></label><br>
        <label>緯度：<input type="text" id="lat" name="lat" readonly></label><br>
        <label>経度：<input type="text" id="lng" name="lng" readonly></label><br>
        <button type="submit">登録する</button>

        <label>混雑度：
            <select name="congestion" required>
            <option value="空いている">空いている</option>
            <option value="やや混み">やや混み</option>
            <option value="混雑">混雑</option>
            </select>
        </label><br>
    </form>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
  var map = L.map('map').setView([35.1709, 136.8815], 17);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
  }).addTo(map);

  // 登録済みスポットをピン表示
  @foreach($spots as $spot)
    L.marker([{{ $spot->lat }}, {{ $spot->lng }}]).addTo(map)
      .bindPopup(`<strong>{{ $spot->name }}</strong><br>{{ $spot->description }}`);
  @endforeach

  // ユーザークリックで仮マーカー＆フォーム反映
  var marker;
  map.on('click', function(e) {
    var lat = e.latlng.lat.toFixed(7);
    var lng = e.latlng.lng.toFixed(7);
    document.getElementById('lat').value = lat;
    document.getElementById('lng').value = lng;

    if (marker) {
      marker.setLatLng(e.latlng);
    } else {
      marker = L.marker(e.latlng).addTo(map);
    }
  });
</script>