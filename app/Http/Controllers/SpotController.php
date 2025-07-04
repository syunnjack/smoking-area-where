<?php

namespace App\Http\Controllers;

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
    if ($request->filled('congestion')) {
        $query->where('congestion', $request->congestion);
    }

    // 一度全スポット取得（orderによって並べ替える）
    $spots = $query->get();

    // 並び替え処理（nearby or popular）
    if ($request->filled('order')) {
        if ($request->order === 'nearby' && $request->has(['lat', 'lng'])) {
            $lat = floatval($request->lat);
            $lng = floatval($request->lng);
            $spots = $spots->sortBy(function ($spot) use ($lat, $lng) {
                return pow($spot->lat - $lat, 2) + pow($spot->lng - $lng, 2); // 近い順（距離の2乗）
            });
        } elseif ($request->order === 'popular' && $spots->first() && isset($spots->first()->views)) {
            $spots = $spots->sortByDesc('views');
        }
    }
    if ($request->order === 'popular') {
    $spots = $spots->sortByDesc('views');
}



    $areas = Spot::distinct()->pluck('area');

    return view('spots.index', compact('spots', 'areas'));


}

public function create()
{
    $spots = Spot::all();
    return view('spots.create',compact('spots'));
}

public function store(Request $request)
{
    
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'congestion' => 'nullable|string|max:255',
        'lat' => 'required|numeric',
        'lng' => 'required|numeric',
        // 他の項目もあれば追記
    ]);

    Spot::create($validated);

    return redirect()->route('spots.index')->with('success', '喫煙所を登録しました');


}
public function show(Spot $spot)
{
    
    $spot->increment('views');
    $spot->refresh();          // ✅ そのあと最新データを取得


    return view('spots.show', compact('spot'));
}



public function edit(Spot $spot)
{
    return view('spots.edit', compact('spot'));
}

public function update(Request $request, Spot $spot)
{
    //$spot->update($request->only(['name', 'description']));
    $congestionOptions = ['空いてる', 'やや混み', '混んでる'];
    

    $data = $request->validate([


    'name' => 'required|string|max:255',
    'description' => 'nullable|string',
    'area' => 'nullable|string',
    'lat' => 'required|numeric',
    'lng' => 'required|numeric',
    'congestion' => 'nullable|string|in:' . implode(',', $congestionOptions),
]);
    $spot->update($data); // ✅ 保存を実行する！




    return redirect()
    ->route('spots.show', $spot->id)
    ->with('success', '喫煙所を更新しました。');
}



}
