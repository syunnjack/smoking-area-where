<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ContentModeration
{
    private const NG_WORDS = ['死ね', '殺す', 'バカ', 'カス', 'http://', 'https://', 'www.'];

    public static function containsNgWord(string $text): bool
    {
        foreach (self::NG_WORDS as $word) {
            if ($word !== '' && mb_stripos($text, $word) !== false) {
                return true;
            }
        }

        return false;
    }

    public static function clientIpHash(Request $request): string
    {
        return hash('sha256', $request->ip() ?? 'unknown');
    }

    /**
     * 同一IP・同一キーからの連投を防ぐ簡易クールダウン。
     * 初回呼び出し時はfalseを返しキーを記録、期間内の再呼び出しはtrueを返す。
     */
    public static function isTooSoon(string $key, int $seconds): bool
    {
        if (Cache::has($key)) {
            return true;
        }

        Cache::put($key, true, $seconds);

        return false;
    }
}
