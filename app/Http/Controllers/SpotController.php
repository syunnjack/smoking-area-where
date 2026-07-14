<?php

namespace App\Http\Controllers;

use App\Helpers\CongestionHelper;
use App\Support\ContentModeration;
use App\Support\LineMessaging;
use Illuminate\Http\Request;
use App\Models\Spot;

class SpotController extends Controller
{
    public function index(Request $request)
    {
        $query = Spot::query();

        // フィルター処理
        if ($request->filled('area')) {
            $query->where('area', $request->area);
        }
        // 混雑度は、reportsがあればaverage_congestionでフィルタリング
        if ($request->filled('congestion')) {
            if ($request->congestion === 'empty') {
                $query->whereBetween('average_congestion', [1.0, 1.5]);
            } elseif ($request->congestion === 'slightly_crowded') {
                $query->whereBetween('average_congestion', [1.5, 2.5]);
            } elseif ($request->congestion === 'crowded') {
                $query->whereBetween('average_congestion', [2.5, 3.5]);
            } elseif ($request->congestion === 'very_crowded') {
                $query->whereBetween('average_congestion', [3.5, 4.0]);
            }
        }

        $spots = $query->get(); // まずフィルターを適用して取得

        // 並び替え処理（nearby or popular）
        if ($request->filled('order')) {
            if ($request->order === 'nearby' && $request->has(['lat', 'lng'])) {
                $lat = floatval($request->lat);
                $lng = floatval($request->lng);
                $spots = $spots->sortBy(function ($spot) use ($lat, $lng) {
                    // 緯度経度から距離を計算（より正確な計算が必要ならHaversine式などを使用）
                    return pow($spot->lat - $lat, 2) + pow($spot->lng - $lng, 2);
                });
            } elseif ($request->order === 'popular') {
                // popular順はviewsで並び替え。viewsフィールドがない場合は注意
                $spots = $spots->sortByDesc('views');
            } elseif ($request->order === 'likes') { // いいねが多い順
                $spots = $spots->sortByDesc('likes_count');
            } elseif ($request->order === 'reviews') { // 口コミが多い順 (Reviewモデルのリレーションが必要)
                $spots = $spots->sortByDesc(function($spot) {
                    return $spot->reviews->count();
                });
            }
        }

        $areas = Spot::distinct()->pluck('area'); // エリアフィルター用

        return view('spots.index', compact('spots', 'areas'));
    }

    public function create()
    {
        $spots = Spot::all(); // DBから全件取得
        return view('spots.create',compact('spots'));
    }

    public function store(Request $request)
    {
        // ハニーポット: ボットはこの隠しフィールドを埋めてしまう
        if (!empty($request->input('website'))) {
            return redirect()->route('spots.thanks');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'area' => 'nullable|string|max:255',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        if (ContentModeration::containsNgWord($validated['name'] . ' ' . ($validated['description'] ?? ''))) {
            return back()->withErrors(['name' => '投稿内容に使用できない文字列が含まれています。'])->withInput();
        }

        $ipHash = ContentModeration::clientIpHash($request);
        if (ContentModeration::isTooSoon("spot-create:{$ipHash}", 30)) {
            return back()->withErrors(['name' => '投稿間隔が短すぎます。しばらく待ってから再度お試しください。'])->withInput();
        }

        Spot::create($validated);

        return redirect()->route('spots.thanks');
    }

    public function show(Spot $spot)
    {
        // 口コミを最新順（created_at の降順）で読み込むように修正
    $spot->load(['reviews' => function ($query) {
        $query->orderBy('created_at', 'desc');
    }]);

    // ビューにデータを渡す
    return view('spots.show', compact('spot'));
    }

    public function edit(Spot $spot)
    {
        return view('spots.edit', compact('spot'));
    }

    public function update(Request $request, Spot $spot)
    {
        $data = $request->validate([
            'description' => 'nullable|string',
            'area' => 'nullable|string|max:255',
            'congestion' => 'nullable|string|in:空いてる,やや混み,混んでる,非常に混んでる',
        ]);

        $spot->update($data);

        return redirect()
            ->route('spots.show', $spot->id)
            ->with('success', '喫煙所を更新しました。');
    }

    /**
     * 混雑度を匿名ユーザーから報告を受け付ける
     */
    public function reportCongestion(Request $request, Spot $spot)
    {
        $ipHash = ContentModeration::clientIpHash($request);
        if (ContentModeration::isTooSoon("congestion:{$spot->id}:{$ipHash}", 60)) {
            return response()->json(['error' => '報告間隔が短すぎます。しばらく待ってから再度お試しください。'], 429);
        }

        $validated = $request->validate([
            'level' => 'required|in:empty,slightly_crowded,crowded,very_crowded',
        ]);

        $levelMap = ['empty' => 1, 'slightly_crowded' => 2, 'crowded' => 3, 'very_crowded' => 4];
        $numericLevel = $levelMap[$validated['level']];

        $previousBucket = CongestionHelper::getText($spot->average_congestion);

        $reports = json_decode($spot->congestion_reports ?? '[]', true);
        $reports[] = $numericLevel;

        $average = array_sum($reports) / count($reports);

        $spot->congestion_reports = json_encode($reports);
        $spot->average_congestion = round($average, 2);
        $spot->save();

        $newBucket = CongestionHelper::getText($spot->average_congestion);
        if ($newBucket !== $previousBucket) {
            $this->notifyFavoritesOfCongestionChange($spot, $newBucket);
        }

        return response()->json(['average_congestion' => $spot->average_congestion]);
    }

    /**
     * お気に入り登録しているLINEユーザーに混雑度の変化を通知する
     */
    private function notifyFavoritesOfCongestionChange(Spot $spot, string $newBucket): void
    {
        $spot->loadMissing('favorites.lineUser');

        foreach ($spot->favorites as $favorite) {
            if (! $favorite->lineUser) {
                continue;
            }

            LineMessaging::push(
                $favorite->lineUser->line_user_id,
                "「{$spot->name}」の混雑状況が「{$newBucket}」に変わりました。"
            );
        }
    }

    /**
     * 喫煙所に「いいね！」を追加する
     */
    public function like(Request $request, Spot $spot)
    {
        $ipHash = ContentModeration::clientIpHash($request);
        if (ContentModeration::isTooSoon("like:{$spot->id}:{$ipHash}", 60)) {
            return response()->json(['error' => 'いいね！は少し時間を空けてから再度お試しください。'], 429);
        }

        $spot->increment('likes_count');
        $spot->refresh();

        return response()->json(['likes_count' => $spot->likes_count]);
    }

    /**
     * 全喫煙所ページを含むsitemap.xmlを動的に生成する
     */
    public function sitemap()
    {
        $spots = Spot::select('id', 'updated_at')->get();

        $xml = view('sitemap', compact('spots'))->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}