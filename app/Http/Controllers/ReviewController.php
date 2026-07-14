<?php

namespace App\Http\Controllers;

use App\Support\ContentModeration;
use Illuminate\Http\Request;
use App\Models\Spot;

class ReviewController extends Controller
{
    public function store(Request $request, Spot $spot)
    {
        // ハニーポット: ボットはこの隠しフィールドを埋めてしまう
        if (!empty($request->input('website'))) {
            return back()->with('success', '口コミを投稿しました。');
        }

        $validated = $request->validate([
            'content' => 'required|string|min:2|max:500',
        ]);

        if (ContentModeration::containsNgWord($validated['content'])) {
            return back()->withErrors(['content' => '投稿内容に使用できない文字列が含まれています。'])->withInput();
        }

        $ipHash = ContentModeration::clientIpHash($request);
        if (ContentModeration::isTooSoon("review:{$spot->id}:{$ipHash}", 30)) {
            return back()->withErrors(['content' => '投稿間隔が短すぎます。しばらく待ってから再度お試しください。'])->withInput();
        }

        $spot->reviews()->create([
            'content' => $validated['content'],
        ]);

        return back()->with('success', '口コミを投稿しました。');
    }
}
