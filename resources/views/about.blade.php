@extends('layouts.plain')

@section('title', 'このサイトについて | ' . config('app.name'))
@section('description', config('app.name') . 'の運営方針、データの集め方、口コミの取り扱いについて説明しています。')

@section('content')
<div class="container my-4" style="max-width: 720px;">
  <h1 class="h4 fw-bold mb-4">このサイトについて</h1>

  <section class="mb-4">
    <h2 class="h6">サイトの目的</h2>
    <p class="text-muted small">
      「{{ config('app.name') }}」は、喫煙所の場所を地図から探せる投稿型マップです。新しい喫煙所は誰でもログイン不要・匿名で投稿でき、
      実際に利用した人が混雑度の報告や口コミ、いいねを行うことで情報が更新されていきます。
    </p>
  </section>

  <section class="mb-4">
    <h2 class="h6">掲載データについて</h2>
    <p class="text-muted small">
      サイト開設時点の初期データの一部は、公開されている喫煙所情報をもとに収集しています。以降に追加された喫煙所は、
      すべて利用者からの投稿によるものです。位置情報や名称に誤りがある場合や、施設の閉鎖・移転があった場合は、
      各喫煙所ページから修正のご連絡をいただけると助かります。
    </p>
  </section>

  <section class="mb-4">
    <h2 class="h6">混雑度について</h2>
    <p class="text-muted small">
      混雑度は、その場を訪れた利用者が「空いている」「やや混雑」「混雑」のいずれかを匿名で報告した値を平均して表示しています。
      リアルタイムの正確な状況を保証するものではなく、あくまで参考情報としてご利用ください。
    </p>
  </section>

  <section class="mb-4">
    <h2 class="h6">口コミ・投稿について</h2>
    <p class="text-muted small">
      口コミや新規スポットの投稿は、どなたでもログイン不要で行えます。投稿内容は運営による事前確認を行わず即時反映されますが、
      不適切な投稿を発見した場合は内容を精査のうえ削除などの対応を行います。口コミはあくまで投稿者個人の感想であり、
      当サイトが内容の正確性を保証するものではありません。
    </p>
  </section>

  <a href="{{ route('spots.index') }}" class="d-block text-center text-muted mt-4">トップページに戻る</a>
</div>
@endsection
