<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Spot;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function toggle(Request $request, Spot $spot)
    {
        $lineUserLocalId = $request->session()->get('line_user_local_id');

        if (! $lineUserLocalId) {
            return redirect()->route('line.login', ['spot' => $spot->id]);
        }

        $favorite = Favorite::where('line_user_id', $lineUserLocalId)
            ->where('spot_id', $spot->id)
            ->first();

        if ($favorite) {
            $favorite->delete();

            return back()->with('success', '通知登録を解除しました。');
        }

        Favorite::create([
            'line_user_id' => $lineUserLocalId,
            'spot_id' => $spot->id,
        ]);

        return back()->with('success', '混雑度が変わるとLINEでお知らせします。');
    }
}
