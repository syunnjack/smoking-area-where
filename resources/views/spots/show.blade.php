@extends('layouts.plain')

@section('title', $spot->name . ' の喫煙所情報・混雑度・口コミ | ' . config('app.name'))
@section('description', $spot->name . '（' . ($spot->area ?? '喫煙所') . '）の場所・混雑度・利用者の口コミを確認できます。' . mb_substr($spot->description ?? '', 0, 60))

@push('structured-data')
<script type="application/ld+json">
{!! json_encode([
  '@context' => 'https://schema.org',
  '@type' => 'BreadcrumbList',
  'itemListElement' => [
      ['@type' => 'ListItem', 'position' => 1, 'name' => config('app.name'), 'item' => url('/')],
      ['@type' => 'ListItem', 'position' => 2, 'name' => $spot->name, 'item' => url("/spots/{$spot->id}")],
  ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
<script type="application/ld+json">
{!! json_encode(array_filter([
  '@context' => 'https://schema.org',
  '@type' => 'Place',
  'name' => $spot->name,
  'description' => $spot->description,
  'geo' => [
      '@type' => 'GeoCoordinates',
      'latitude' => $spot->lat,
      'longitude' => $spot->lng,
  ],
  'address' => $spot->area,
]), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

@section('content')
<div class="container my-4">
    <div class="card shadow-sm">
        <div class="card-body p-4">
            {{-- スポット情報 --}}
            <h1 class="h3 fw-bold mb-3">{{ $spot->name }}</h1>
            <p class="text-muted mb-2">{{ $spot->description }}</p>
            <p class="text-secondary small mb-2">緯度: {{ $spot->lat }}, 経度: {{ $spot->lng }}</p>
            @if($spot->area)
                <p class="text-secondary small mb-4">エリア: {{ $spot->area }}</p>
            @endif
            {{-- トップページに戻るボタン --}}
    <div class="mb-3">
        <a href="{{ url('/') }}" class="btn btn-secondary">トップページに戻る</a>
    </div>
            {{-- 混雑度表示 --}}
            <h2 class="h5 mb-2">
                現在の平均混雑度: <span id="currentAverageCongestion" class="text-primary fw-bold">
                    @if($spot->average_congestion !== null)
                        {{ $spot->average_congestion }} ({{ \App\Helpers\CongestionHelper::getText($spot->average_congestion) }})
                    @else
                        報告なし
                    @endif
                </span>
            </h2>

            {{-- 混雑度報告ボタン (Bootstrap ボタン) --}}
            <h3 class="h6 mt-4 mb-2">混雑度を報告する</h3>
            <div id="congestionButtons" data-spot-id="{{ $spot->id }}" class="d-flex gap-2 mb-4">
                <button data-level="empty" class="btn btn-success">空いている</button>
                <button data-level="slightly_crowded" class="btn btn-warning">やや混雑</button>
                <button data-level="crowded" class="btn btn-danger">混雑</button>
            </div>
            <p id="congestionMessage" class="text-success small"></p>

            {{-- いいね！機能 (Bootstrap ボタン) --}}
            <div class="d-flex align-items-center mt-4 mb-4">
                <button id="likeButton" data-spot-id="{{ $spot->id }}" class="btn btn-primary me-2">
                    いいね！
                </button>
                <span id="likesCount" class="h4 fw-bold mb-0">{{ $spot->likes_count }}</span> <span class="text-muted ms-1">件のいいね！</span>
            </div>

            {{-- 口コミ投稿フォーム --}}
            <h3 class="h6 mt-4 mb-2">口コミを投稿する</h3>

            @if ($errors->any())
                <div class="alert alert-danger py-2 small">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif
            @if (session('success'))
                <div class="alert alert-success py-2 small">{{ session('success') }}</div>
            @endif

            <form id="reviewForm" action="{{ route('spots.reviews.store', $spot->id) }}" method="POST" class="bg-light p-3 rounded shadow-sm">
                @csrf

                {{-- ハニーポット: 人間には見えない項目。ボットが埋めた場合は投稿を無視する --}}
                <div style="position:absolute; left:-9999px;" aria-hidden="true">
                    <label>ウェブサイト<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                </div>

                <textarea name="content" placeholder="ここに口コミを入力してください" rows="3" class="form-control mb-2" required minlength="2" maxlength="500">{{ old('content') }}</textarea>
                <button type="submit" class="btn btn-dark">口コミを送信</button>
            </form>

            {{-- 口コミ一覧 --}}
            <h3 class="h6 mt-5 mb-3">口コミ</h3>
            <div id="reviewList">
                @forelse($spot->reviews as $review)
                    <div class="card mb-3 bg-light">
                        <div class="card-body">
                            <p class="mb-2">{{ $review->content }}</p>
                            <small class="text-muted">投稿日: {{ $review->created_at->format('Y/m/d H:i') }}</small>
                        </div>
                    </div>
                @empty
                    <p class="text-muted">まだ口コミはありません。</p>
                @endforelse
            </div>

            {{-- OpenStreetMapへの提案 --}}
            <p class="text-secondary small mt-5">
                この情報はOpenStreetMapの更新にも役立ちます。<br>
                <a href="https://www.openstreetmap.org/edit?editor=id&lat={{ $spot->lat }}&lon={{ $spot->lng }}&zoom=18" target="_blank">OpenStreetMapで編集する (外部サイト)</a>
            </p>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // 混雑度テキスト変換ヘルパー関数
    function getCongestionText(avg) {
        if (avg === null || isNaN(avg)) return '不明';
        if (avg >= 2.5) return '混雑';
        if (avg >= 1.5) return 'やや混雑';
        return '空いている';
    }

    document.addEventListener('DOMContentLoaded', function() {

        // 混雑度報告機能
        const congestionButtonsDiv = document.getElementById('congestionButtons');
        const congestionMessage = document.getElementById('congestionMessage');
        const currentAverageCongestionSpan = document.getElementById('currentAverageCongestion');

        if (congestionButtonsDiv) {
            const spotId = congestionButtonsDiv.dataset.spotId;

            congestionButtonsDiv.addEventListener('click', async function(event) {
                if (event.target.tagName === 'BUTTON') {
                    const level = event.target.dataset.level;

                    try {
                        const response = await fetch(`/spots/${spotId}/congestion`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ level: level })
                        });

                        if (!response.ok) {
                            const errorData = await response.json();
                            throw new Error(errorData.message || errorData.error || '混雑度報告に失敗しました。');
                        }

                        const data = await response.json();
                        currentAverageCongestionSpan.textContent = `${data.average_congestion} (${getCongestionText(data.average_congestion)})`;
                        congestionMessage.textContent = '混雑度を報告しました！';
                        setTimeout(() => congestionMessage.textContent = '', 3000);

                    } catch (error) {
                        console.error('Error reporting congestion:', error);
                        congestionMessage.textContent = 'エラー: ' + error.message;
                        congestionMessage.classList.remove('text-success');
                        congestionMessage.classList.add('text-danger');
                    }
                }
            });
        }


        // いいね！機能のスクリプト
        const likeButton = document.getElementById('likeButton');
        const likesCountSpan = document.getElementById('likesCount');

        if (likeButton) {
            const spotId = likeButton.dataset.spotId;

            likeButton.addEventListener('click', async function() {
                try {
                    const response = await fetch(`/spots/${spotId}/like`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    if (!response.ok) {
                        const errorData = await response.json();
                        throw new Error(errorData.message || errorData.error || 'いいね！に失敗しました。');
                    }

                    const data = await response.json();
                    likesCountSpan.textContent = data.likes_count; // いいね数を更新

                } catch (error) {
                    console.error('Error liking spot:', error);
                    alert('いいね！の処理中にエラーが発生しました: ' + error.message);
                }
            });
        }
    });
</script>
@endsection
