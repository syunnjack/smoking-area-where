@extends('layouts.plain')

@section('content')
@if (session('success'))
  <div class="alert alert-success text-center mb-3">
    {{ session('success') }}
  </div>
@endif

<div class="mb-4">
  <a href="{{ route('spots.index') }}" class="btn btn-outline-secondary">
    ← スポット一覧に戻る
  </a>
</div>
  {{-- 🔹 喫煙所情報 --}}
  <h3 class="mb-3">{{ $spot->name }}</h3>

  <p>{{ $spot->description }}</p>

  @if($spot->area)
    <span class="badge bg-secondary">{{ $spot->area }}</span>
  @endif

  <span class="badge bg-info">📈 閲覧数：{{ $spot->views }}</span>
  @if ($spot->congestion)
  <span class="badge bg-info">🚶 混雑状況：{{ $spot->congestion }}</span>
@else
  <span class="text-muted">未登録</span>
@endif


  {{-- ✏️ 編集ボタン --}}
  <div class="my-3">
    <a href="{{ route('spots.edit', $spot->id) }}" class="btn btn-outline-secondary btn-sm">
      ✏️ この喫煙所を編集する
    </a>
  </div>

  {{-- 💬 コメント投稿フォーム --}}
<h5>この場所どうだった？</h5>
<form id="review-form" action="{{ route('spots.reviews.store', $spot->id) }}" method="POST">
  @csrf

  {{-- ⭐ 評価の選択ボタン --}}
  <div class="d-flex gap-2 mb-2">
    @for ($i = 1; $i <= 5; $i++)
      <button type="button" class="btn btn-outline-warning btn-sm rating-btn" data-rating="{{ $i }}">
        {{ str_repeat('★', $i) }}
      </button>
    @endfor
  </div>

  {{-- ⭐ 選択された評価を保持する hidden フィールド --}}
  <input type="hidden" name="rating" id="rating-value">

  {{-- 💬 コメント欄 --}}
  <div class="mb-2">
    <textarea name="comment" class="form-control" rows="2" placeholder="コメントを入力（任意）"></textarea>
  </div>

  <button type="submit" class="btn btn-outline-primary btn-sm">送信</button>
</form>

  {{-- 📋 コメント一覧 --}}
  <h5 class="mt-4">📝 コメント一覧</h5>
  <ul id="comment-list" class="list-group">
    @foreach ($spot->reviews()->latest()->get() as $review)
      <li class="list-group-item">
        @if ($review->rating)
          <span class="text-warning me-1">{{ str_repeat('★', $review->rating) }}</span>
        @endif
        @if($review->comment)
        {{ $review->comment }}
          @endif
        <span class="text-muted float-end small">{{ $review->created_at->format('Y/m/d H:i') }}</span>
      </li>
    @endforeach
  </ul>
<div class="mt-4 text-center">
  <a href="{{ route('spots.index') }}" class="btn btn-outline-secondary">
    ← スポット一覧へ戻る
  </a>
</div>
</div>
@section('scripts')
<script>
  function disableSubmit(form) {
    form.querySelector('button[type="submit"]').disabled = true;
  }

  document.addEventListener('DOMContentLoaded', function () {
    const ratingInput = document.getElementById('rating-value');
    const ratingButtons = document.querySelectorAll('.rating-btn');

    ratingButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        const rating = btn.dataset.rating;
        ratingInput.value = rating;

        ratingButtons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
      });
    });

    document.getElementById('review-form').addEventListener('submit', function (e) {
      if (!ratingInput.value) {
        e.preventDefault();
        alert("⭐ 評価を選んでから送信してください。");
      }
    });
  });
</script>
@endsection

        




