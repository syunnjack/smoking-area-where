@extends('layouts.plain')

@section('content')
<div class="container my-5">
  <h3 class="mb-4">✏️ 喫煙所を編集</h3>

  <form method="POST" action="{{ route('spots.update', $spot->id) }}">
    @csrf
    @method('PATCH')

    <div class="mb-3">
      <label class="form-label">名称</label>
      <input type="text" name="name" value="{{ old('name', $spot->name) }}" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">ひとことコメント</label>
      <textarea name="description" rows="2" class="form-control">{{ old('description', $spot->description) }}</textarea>
    </div>
      <div class="mb-3">
  <label for="congestion" class="form-label">🚶 混雑状況</label>
  <select name="congestion" id="congestion" class="form-select">
    <option value="">未登録（空欄のまま）</option>
    <option value="空いてる" {{ $spot->congestion == '空いてる' ? 'selected' : '' }}>空いてる</option>
    <option value="やや混み" {{ $spot->congestion == 'やや混み' ? 'selected' : '' }}>やや混み</option>
    <option value="混んでる" {{ $spot->congestion == '混んでる' ? 'selected' : '' }}>混んでる</option>
  </select>
</div>
    <button type="submit" class="btn btn-primary">更新する</button>
  </form>
</div>
@endsection

