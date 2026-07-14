<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\LineUser;
use App\Models\Spot;
use App\Support\LineMessaging;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LineLoginController extends Controller
{
    public function redirect(Request $request)
    {
        $state = Str::random(40);
        $request->session()->put('line_login_state', $state);

        if ($request->filled('spot')) {
            $request->session()->put('line_login_intended_spot', (int) $request->input('spot'));
        }

        return redirect()->away(LineMessaging::authorizeUrl($state));
    }

    public function callback(Request $request)
    {
        $state = $request->query('state');
        $expectedState = $request->session()->pull('line_login_state');

        if (! $state || $state !== $expectedState) {
            return redirect()->route('spots.index')->withErrors(['line' => 'LINEログインの検証に失敗しました。もう一度お試しください。']);
        }

        if (! $request->filled('code')) {
            return redirect()->route('spots.index')->withErrors(['line' => 'LINEログインがキャンセルされました。']);
        }

        $token = LineMessaging::exchangeToken($request->input('code'));
        $claims = LineMessaging::verifyIdToken($token['id_token']);

        $lineUser = LineUser::updateOrCreate(
            ['line_user_id' => $claims['sub']],
            ['display_name' => $claims['name'] ?? null]
        );

        // セッションにはline_usersテーブルのローカルIDを保持する
        // (LINE側のuserIdそのものではない点に注意)
        $request->session()->put('line_user_local_id', $lineUser->id);

        $intendedSpotId = $request->session()->pull('line_login_intended_spot');
        if ($intendedSpotId) {
            $spot = Spot::find($intendedSpotId);
            if ($spot) {
                Favorite::firstOrCreate([
                    'line_user_id' => $lineUser->id,
                    'spot_id' => $spot->id,
                ]);

                return redirect()->route('spots.show', $spot)->with('success', '通知登録が完了しました。混雑度が変わるとLINEでお知らせします。');
            }
        }

        return redirect()->route('spots.index')->with('success', 'LINEログインが完了しました。');
    }
}
