<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SpotController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\LineLoginController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\LineWebhookController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 認証ユーザーのみが喫煙所を編集・更新できるようにする
    Route::get('/spots/{spot}/edit', [SpotController::class, 'edit'])->name('spots.edit');
    Route::patch('/spots/{spot}', [SpotController::class, 'update'])->name('spots.update');
});


// --- 匿名ユーザーも利用できるルート ---

// 喫煙所の一覧表示 (トップページ)
Route::get('/', [SpotController::class, 'index'])->name('spots.index');

// 新しい喫煙所の投稿
Route::get('/create', [SpotController::class, 'create'])->name('spots.create');
Route::post('/spots', [SpotController::class, 'store'])->name('spots.store')->middleware('throttle:5,1');

// 個別喫煙所の詳細表示
Route::get('/spots/{spot}', [SpotController::class, 'show'])->name('spots.show');

// 喫煙所へのコメント・評価（いいね！）投稿（匿名OK）
Route::post('/spots/{spot}/reviews', [ReviewController::class, 'store'])->name('spots.reviews.store')->middleware('throttle:10,1');

// 喫煙所に「いいね！」を投稿（匿名OK）
Route::post('/spots/{spot}/like', [SpotController::class, 'like'])->name('spots.like')->middleware('throttle:30,1');

// 混雑度報告（匿名OK）
Route::post('/spots/{spot}/congestion', [SpotController::class, 'reportCongestion'])->name('spots.congestion.report')->middleware('throttle:30,1');


Route::view('/thanks', 'spots.thanks')->name('spots.thanks'); // 投稿完了ページなど

// このサイトについて
Route::view('/about', 'about')->name('about');

// SEO用ファイル
Route::get('/sitemap.xml', [SpotController::class, 'sitemap'])->name('sitemap');

// LINE連携（お気に入りスポットの混雑度通知）
Route::get('/line/login', [LineLoginController::class, 'redirect'])->name('line.login');
Route::get('/line/callback', [LineLoginController::class, 'callback'])->name('line.callback');
Route::post('/spots/{spot}/favorite', [FavoriteController::class, 'toggle'])
    ->name('spots.favorite.toggle')
    ->middleware('throttle:10,1');
Route::post('/line/webhook', [LineWebhookController::class, 'handle'])->name('line.webhook');

require __DIR__.'/auth.php'; // 認証関連のルート