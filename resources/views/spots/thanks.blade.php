@extends('layouts.plain')

@section('content')
<div class="container text-center mt-5">
  <h2 class="mb-3">🙌 投稿ありがとうございます！</h2>
  <p class="mb-4 text-muted">喫煙所マップに反映されました。</p>
  <a href="{{ url('/') }}" class="btn btn-primary">トップへ戻る</a>
</div>
@endsection

