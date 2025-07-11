@extends('layouts.plain')

@section('title', $spot->name . ' - 喫煙所マップ')

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
            <h4 class="mb-2">
                現在の平均混雑度: <span id="currentAverageCongestion" class="text-primary fw-bold">
                    @if($spot->average_congestion !== null)
                        {{ $spot->average_congestion }} ({{ \App\Helpers\CongestionHelper::getText($spot->average_congestion) }})
                    @else
                        報告なし
                    @endif
                </span>
            </h4>

            {{-- 混雑度報告ボタン (Bootstrap ボタン) --}}
            <h5 class="mt-4 mb-2">混雑度を報告する</h5>
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
            <h5 class="mt-4 mb-2">口コミを投稿する</h5>
            <form id="reviewForm" action="{{ route('spots.reviews.store', $spot->id) }}" method="POST" class="bg-light p-3 rounded shadow-sm">
                @csrf
                <textarea name="content" placeholder="ここに口コミを入力してください（任意）" rows="3" class="form-control mb-2"></textarea>
                <button type="submit" class="btn btn-dark">口コミを送信</button>
            </form>
            
            {{-- 口コミ一覧 --}}
            <h5 class="mt-5 mb-3">口コミ</h5>
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
        if (avg >= 3.5) return '非常に混雑';
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
                            throw new Error(errorData.message || '混雑度報告に失敗しました。');
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
                        throw new Error(errorData.message || 'いいね！に失敗しました。');
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