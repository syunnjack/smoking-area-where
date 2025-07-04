<?php

namespace App\Http\Controllers;
use App\Models\Spot;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Spot $spot){

        

        // ⬇️ これが一番最初
    $data = $request->validate([
        'rating' => 'required|integer|min:1|max:5',
        'comment' => 'nullable|string|max:1000',
    ]);

    $data['spot_id'] = $spot->id;

    Review::create($data); // もしくは $spot->reviews()->create($data); どちらか1つでOK

    return back()->with('success', 'レビューを投稿しました。');
}
}

