<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Spot;
use App\Models\Review;

class ReviewController extends Controller
{
    public function store(Request $request, Spot $spot)
    {
        $validated = $request->validate([
            'content' => 'nullable|string|max:500',
            // 'rating' => 'nullable|integer|min:1|max:5', // rating を使用する場合
        ]);

        // content と rating が両方とも空の場合、投稿を許可しないなどのバリデーションロジックを追加可能
        // if (empty($validated['content']) && (!isset($validated['rating']) || is_null($validated['rating']))) {
        //     return back()->withErrors(['message' => 'コメントまたは評価を入力してください。'])->withInput();
        // }

        $spot->reviews()->create([
            'content' => $validated['content'] ?? null,
            // 'rating' => $validated['rating'] ?? null, // rating を使用する場合
        ]);

        return back()->with('success', '口コミを投稿しました。');
    }
}